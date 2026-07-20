<?php

namespace App\Services;

use App\Models\AppSetting;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Crypt;

class GoogleDriveService
{
    private ?Client $client = null;
    private ?Drive $service = null;

    public function getClient(): Client
    {
        if ($this->client) {
            return $this->client;
        }

        $this->client = new Client();
        $this->client->setClientId(config('backup.google.client_id'));
        $this->client->setClientSecret(config('backup.google.client_secret'));
        $this->client->setRedirectUri(config('backup.google.redirect_uri'));
        $this->client->addScope(Drive::DRIVE_FILE);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');

        $refreshToken = $this->getStoredRefreshToken();
        if ($refreshToken) {
            $this->client->setRefreshToken($refreshToken);
        }

        return $this->client;
    }

    public function getService(): Drive
    {
        if ($this->service) {
            return $this->service;
        }

        $this->service = new Drive($this->getClient());

        return $this->service;
    }

    public function getAuthUrl(): string
    {
        return $this->getClient()->createAuthUrl();
    }

    public function handleCallback(string $code): void
    {
        $this->getClient()->fetchAccessTokenWithAuthCode($code);
        $token = $this->getClient()->getAccessToken();

        if (isset($token['refresh_token'])) {
            $this->storeRefreshToken($token['refresh_token']);
        }
    }

    public function isConnected(): bool
    {
        return (bool) $this->getStoredRefreshToken();
    }

    public function disconnect(): void
    {
        AppSetting::remove('google_drive_refresh_token');
    }

    public function getStoredRefreshToken(): ?string
    {
        $encrypted = AppSetting::get('google_drive_refresh_token');

        return $encrypted ? Crypt::decryptString($encrypted) : null;
    }

    public function storeRefreshToken(string $token): void
    {
        AppSetting::set('google_drive_refresh_token', Crypt::encryptString($token));
    }

    public function getOrCreateFolder(string $name): string
    {
        $service = $this->getService();

        $query = "mimeType='application/vnd.google-apps.folder' and name='" . addslashes($name) . "' and trashed=false";
        $results = $service->files->listFiles([
            'q' => $query,
            'fields' => 'files(id, name)',
            'spaces' => 'drive',
        ]);

        if (count($results->getFiles()) > 0) {
            return $results->getFiles()[0]->getId();
        }

        $folderMetadata = new DriveFile([
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
        ]);

        $folder = $service->files->create($folderMetadata, [
            'fields' => 'id',
        ]);

        return $folder->getId();
    }

    public function uploadFile(string $filePath, string $name, string $folderId): array
    {
        $service = $this->getService();

        $fileMetadata = new DriveFile([
            'name' => $name,
            'parents' => [$folderId],
        ]);

        $content = file_get_contents($filePath);
        $file = $service->files->create($fileMetadata, [
            'data' => $content,
            'uploadType' => 'multipart',
            'fields' => 'id, webViewLink, size',
        ]);

        return [
            'id' => $file->getId(),
            'link' => $file->getWebViewLink(),
            'size' => (int) $file->getSize(),
        ];
    }

    public function deleteFile(string $fileId): bool
    {
        $service = $this->getService();

        try {
            $service->files->delete($fileId);

            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public function testConnection(): bool
    {
        try {
            $service = $this->getService();
            $service->files->listFiles(['maxResults' => 1]);

            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
