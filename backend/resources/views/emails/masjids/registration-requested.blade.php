<!DOCTYPE html>
<html lang="en">
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6">
    <h1>New {{ ucfirst($masjid->type) }} registration request</h1>
    <p>A new registration is waiting for administrator review.</p>
    <table cellpadding="6" cellspacing="0">
        <tr><th align="left">Name</th><td>{{ $masjid->name }}</td></tr>
        <tr><th align="left">Type</th><td>{{ ucfirst($masjid->type) }}</td></tr>
        <tr><th align="left">Registration ID</th><td>{{ $masjid->public_id }}</td></tr>
        <tr><th align="left">JAKIM zone</th><td>{{ $masjid->zone_code }}</td></tr>
        <tr><th align="left">Contact</th><td>{{ $masjid->contact_name }}</td></tr>
        <tr><th align="left">Phone</th><td>{{ $masjid->contact_phone }}</td></tr>
        <tr><th align="left">Email</th><td>{{ $masjid->contact_email ?: 'Not provided' }}</td></tr>
        <tr><th align="left">Address</th><td>{{ $masjid->address ?: 'Not provided' }}</td></tr>
    </table>
    <p><a href="{{ route('admin.masjids.edit', $masjid) }}">Review this registration</a></p>
</body>
</html>
