<!DOCTYPE html>
<html lang="en">
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6">
    <h1>Your {{ $masjid->type }} registration is approved</h1>
    <p>Hello {{ $masjid->contact_name ?: 'there' }},</p>
    <p>{{ $masjid->name }} has been approved for Masjid Smart Screen.</p>
    <p><strong>Registration ID:</strong> {{ $masjid->public_id }}</p>
    <p>Your {{ $masjid->type }} can now use Masjid Smart Screen. The management console is available below.</p>
    <p><a href="{{ route('admin.login') }}">Open the management console</a></p>
</body>
</html>
