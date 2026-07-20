@extends('admin.layout')
@section('title', 'Backups')
@section('subtitle', 'Automated database and storage backups to Google Drive.')
@section('top-action')
    @if($isConnected)
        <form method="POST" action="{{ route('admin.backups.store') }}" style="display:inline">
            @csrf
            <button class="button" type="submit" onclick="this.disabled=true;this.textContent='Backing up...'">↻ Run backup now</button>
        </form>
    @endif
@endsection
@section('content')

@if(!$isConnected)
<div class="panel" style="margin-bottom:24px">
    <div class="panel-body" style="text-align:center;padding:40px 22px">
        <div style="font-size:48px;margin-bottom:16px">☁</div>
        <h2 style="margin-bottom:8px">Connect Google Drive</h2>
        <p style="color:var(--muted);margin-bottom:24px;max-width:480px;margin-left:auto;margin-right:auto">
            Link your Google Drive account to automatically store backups of your database and uploaded files.
        </p>
        <a class="button" href="{{ route('admin.backups.connect') }}">Authorize Google Drive</a>
        <p style="color:var(--muted);font-size:12px;margin-top:16px">
            You will be redirected to Google to approve access. Only backup files will be stored.
        </p>
    </div>
</div>
@else
<div class="stats" style="grid-template-columns:repeat(3,minmax(0,1fr))">
    <article class="stat">
        <span>Google Drive</span>
        <strong style="color:#08665b;font-size:18px">Connected</strong>
    </article>
    <article class="stat">
        <span>Total backups</span>
        <strong>{{ $backups->total() }}</strong>
    </article>
    <article class="stat">
        <span>Last backup</span>
        <strong style="font-size:16px">{{ $backups->first() ? $backups->first()->completed_at->diffForHumans() : 'Never' }}</strong>
    </article>
</div>

<section class="panel">
    <div class="panel-head">
        <h2>Backup history</h2>
        <div class="actions-inline">
            <form method="POST" action="{{ route('admin.backups.prune') }}" style="display:inline">
                @csrf
                <button class="button secondary small" type="submit" onclick="return confirm('Delete all backups older than {{ config('backup.schedule.keep_days', 30) }} days?')">Prune old</button>
            </form>
            <a class="button secondary small" href="{{ route('admin.backups.disconnect') }}" onclick="return confirm('Disconnect Google Drive? Existing backups on Drive will remain but no new backups can be created.')">Disconnect</a>
        </div>
    </div>

    @if($backups->isEmpty())
        <div class="empty">No backups yet. Click "Run backup now" to create your first backup.</div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Backup</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($backups as $backup)
                    <tr>
                        <td>
                            <div class="primary-text">
                                @if($backup->google_drive_link)
                                    <a href="{{ $backup->google_drive_link }}" target="_blank" style="color:var(--emerald);text-decoration:underline">
                                        {{ $backup->database_file ?? 'Backup' }}
                                    </a>
                                @else
                                    {{ $backup->database_file ?? 'Backup' }}
                                @endif
                            </div>
                            <div class="secondary-text">{{ $backup->completed_at?->format('d M Y, h:i A') ?? 'In progress...' }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $backup->type === 'scheduled' ? '' : 'active' }}">{{ $backup->type }}</span>
                        </td>
                        <td>{{ $backup->formattedSize() }}</td>
                        <td>
                            @if($backup->status === 'completed')
                                <span class="badge completed">Completed</span>
                            @elseif($backup->status === 'running')
                                <span class="badge pending">Running</span>
                            @elseif($backup->status === 'failed')
                                <span class="badge suspended">Failed</span>
                            @else
                                <span class="badge">{{ $backup->status }}</span>
                            @endif
                            @if($backup->error)
                                <div class="secondary-text" style="max-width:250px;overflow:hidden;text-overflow:ellipsis" title="{{ $backup->error }}">{{ $backup->error }}</div>
                            @endif
                        </td>
                        <td>{{ $backup->created_at->diffForHumans() }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.backups.destroy', $backup) }}" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button class="button danger small" type="submit" onclick="return confirm('Delete this backup? @if($backup->google_drive_id)It will also be removed from Google Drive.@endif')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pager">
            <span>Showing {{ $backups->firstItem() }}–{{ $backups->lastItem() }} of {{ $backups->total() }}</span>
            <div class="actions-inline">
                @if($backups->previousPageUrl())
                    <a class="button secondary small" href="{{ $backups->previousPageUrl() }}">← Previous</a>
                @endif
                @if($backups->nextPageUrl())
                    <a class="button secondary small" href="{{ $backups->nextPageUrl() }}">Next →</a>
                @endif
            </div>
        </div>
    @endif
</section>

<div class="panel" style="margin-top:24px">
    <div class="panel-head"><h2>Settings</h2></div>
    <div class="panel-body">
        <div class="form-grid">
            <div class="field">
                <label>Schedule</label>
                <input type="text" value="{{ config('backup.schedule.frequency', 'daily') }} at {{ config('backup.schedule.time', '03:00') }}" disabled>
                <small>Backups run automatically via Laravel scheduler.</small>
            </div>
            <div class="field">
                <label>Retention</label>
                <input type="text" value="{{ config('backup.schedule.keep_days', 30) }} days" disabled>
                <small>Backups older than this are auto-pruned.</small>
            </div>
        </div>
    </div>
</div>
@endif

<style>
    .badge.completed { color:#08665b; background:#dff7f1; }
</style>
@endsection
