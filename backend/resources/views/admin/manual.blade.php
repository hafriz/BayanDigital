@extends('admin.layout')
@section('title', 'User Manual')
@section('subtitle', 'Everything you need to know about BayanDigital Masjid Smart Screen.')
@section('content')

<style>
    .manual-toc { display:grid; gap:8px; margin-bottom:32px; }
    .manual-toc a { display:flex; align-items:center; gap:10px; padding:14px 18px; border-radius:14px; border:1px solid var(--line); background:rgba(255,255,255,.7); text-decoration:none; color:var(--ink); font-weight:600; font-size:15px; transition:.15s; }
    .manual-toc a:hover { border-color:var(--emerald); background:rgba(15,118,110,.04); }
    .manual-toc a .num { display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:8px; background:var(--emerald); color:#fff; font-size:13px; font-weight:800; flex-shrink:0; }
    .manual-section { margin-bottom:36px; }
    .manual-section h2 { font-size:20px; letter-spacing:-.03em; margin-bottom:10px; color:var(--ink); }
    .manual-section h3 { font-size:16px; font-weight:700; margin:18px 0 6px; color:var(--ink); }
    .manual-section p, .manual-section li { color:var(--muted); line-height:1.65; font-size:15px; }
    .manual-section ul { padding-left:20px; margin:8px 0; }
    .manual-section li { margin-bottom:4px; }
    .manual-section code { background:rgba(15,118,110,.08); color:var(--emerald); padding:2px 7px; border-radius:6px; font-size:13px; font-weight:600; }
    .manual-note { padding:14px 18px; border-radius:12px; background:#fffbeb; border:1px solid #fde68a; color:#92400e; font-size:14px; line-height:1.55; margin:12px 0; }
    .manual-note b { font-weight:700; }
    .manual-support { display:grid; gap:16px; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); margin-top:12px; }
    .manual-support a { display:flex; align-items:center; gap:12px; padding:18px 20px; border-radius:14px; border:1px solid var(--line); background:rgba(255,255,255,.7); text-decoration:none; color:var(--ink); font-weight:600; font-size:15px; transition:.15s; }
    .manual-support a:hover { border-color:var(--emerald); background:rgba(15,118,110,.04); }
    .manual-support a .icon { display:inline-flex; align-items:center; justify-content:center; width:42px; height:42px; border-radius:10px; font-size:20px; flex-shrink:0; }
    .manual-support a .icon.mail { background:rgba(15,118,110,.1); }
    .manual-support a .icon.coffee { background:rgba(244,201,93,.2); }
    .manual-support a small { display:block; color:var(--muted); font-weight:400; font-size:13px; margin-top:2px; }
    .manual-steps { counter-reset:step; padding-left:0; list-style:none; }
    .manual-steps li { counter-increment:step; position:relative; padding:12px 0 12px 48px; border-left:2px solid var(--line); margin-left:14px; }
    .manual-steps li:last-child { border-left-color:transparent; }
    .manual-steps li::before { content:counter(step); position:absolute; left:-15px; top:10px; width:28px; height:28px; border-radius:50%; background:var(--emerald); color:#fff; font-size:13px; font-weight:800; display:flex; align-items:center; justify-content:center; }
</style>

<section class="panel">
    <div class="panel-head"><h2>Contents</h2></div>
    <div class="manual-toc">
        <a href="#getting-started"><span class="num">1</span>Getting Started</a>
        <a href="#registering"><span class="num">2</span>Registering a Masjid</a>
        <a href="#dashboard"><span class="num">3</span>Admin Dashboard</a>
        <a href="#managing-masjids"><span class="num">4</span>Managing Masjids</a>
        <a href="#screen-content"><span class="num">5</span>Screen Content</a>
        <a href="#tv-devices"><span class="num">6</span>TV Device Setup</a>
        <a href="#users"><span class="num">7</span>User Management</a>
        <a href="#backups"><span class="num">8</span>Backups</a>
        <a href="#support"><span class="num">9</span>Support &amp; Contact</a>
    </div>
</section>

<section class="panel manual-section" id="getting-started">
    <div class="panel-head"><h2>1. Getting Started</h2></div>
    <div style="padding:20px;">
        <p><strong>BayanDigital</strong> is a masjid smart screen management system. It lets you register your masjid or surau, manage the content displayed on smart TV screens, and keep track of prayer times &mdash; all from a single admin panel.</p>

        <h3>How It Works</h3>
        <ul>
            <li><strong>Register</strong> your masjid via the public registration page.</li>
            <li>An <strong>administrator</strong> approves your registration.</li>
            <li>You log in to the <strong>admin panel</strong> and manage your screen content.</li>
            <li>Install the <strong>BayanDigital Android TV app</strong> on your smart TV or Android box.</li>
            <li>Pair the TV device with your masjid using a <strong>6-digit pairing code</strong>.</li>
            <li>The TV screen automatically displays your content and live prayer times.</li>
        </ul>

        <div class="manual-note">
            <b>Note:</b> The admin panel supports two roles. <b>Admins</b> have full access to all features including user management and backups. <b>Operators</b> can manage masjid settings and screen content only.
        </div>
    </div>
</section>

<section class="panel manual-section" id="registering">
    <div class="panel-head"><h2>2. Registering a Masjid</h2></div>
    <div style="padding:20px;">
        <p>Any masjid or surau can register through the public registration page. You do not need an existing account.</p>

        <ol class="manual-steps">
            <li>Go to the <strong>registration page</strong> on the BayanDigital website.</li>
            <li>Fill in your masjid details: <strong>name</strong>, <strong>zone/state</strong>, <strong>contact person</strong>, and <strong>phone number</strong>.</li>
            <li>Submit the form. Your registration will be placed in <strong>pending</strong> status.</li>
            <li>An administrator will review and <strong>approve</strong> your registration.</li>
            <li>Once approved, you will receive login credentials to access the admin panel.</li>
        </ol>

        <div class="manual-note">
            <b>Tip:</b> After approval, you can log in at the admin panel URL provided by your administrator. Make sure to change your password on first login.
        </div>
    </div>
</section>

<section class="panel manual-section" id="dashboard">
    <div class="panel-head"><h2>3. Admin Dashboard</h2></div>
    <div style="padding:20px;">
        <p>The dashboard gives you a quick overview of your system at a glance.</p>

        <h3>Stat Cards</h3>
        <ul>
            <li><strong>Pending review</strong> &mdash; Number of masjid registrations awaiting approval.</li>
            <li><strong>Approved masjids</strong> &mdash; Total approved masjid registrations.</li>
            <li><strong>Active content</strong> &mdash; Number of active screen content items across all masjids.</li>
            <li><strong>Active users</strong> &mdash; Number of active admin/operator accounts.</li>
        </ul>

        <h3>Recent Registrations</h3>
        <p>Below the stats, a table shows the most recent masjid registrations with their zone, status badge, and a link to manage each one.</p>

        <h3>Quick Actions</h3>
        <ul>
            <li><strong>Review registrations</strong> &mdash; Jump directly to the pending registrations list.</li>
            <li><strong>User Manual</strong> &mdash; Access this guide from the sidebar.</li>
        </ul>
    </div>
</section>

<section class="panel manual-section" id="managing-masjids">
    <div class="panel-head"><h2>4. Managing Masjids</h2></div>
    <div style="padding:20px;">
        <p>The <strong>Masjids</strong> section lists all registered masjids and lets you manage their settings.</p>

        <h3>Masjid List</h3>
        <ul>
            <li>View all masjids with their <strong>name</strong>, <strong>zone</strong>, <strong>status</strong>, and <strong>registration date</strong>.</li>
            <li>Filter by status: <code>all</code>, <code>pending</code>, <code>approved</code>, or <code>suspended</code>.</li>
            <li>Search masjids by name using the search bar.</li>
        </ul>

        <h3>Edit Masjid Settings</h3>
        <ul>
            <li>Click <strong>Manage</strong> on any masjid to edit its settings.</li>
            <li>Update the <strong>display name</strong>, <strong>zone</strong>, <strong>contact info</strong>, and <strong>prayer time source</strong>.</li>
            <li>Change the masjid's <strong>status</strong> (approve, suspend, or reject).</li>
            <li>Set <strong>display preferences</strong> such as the screen theme and layout.</li>
        </ul>

        <div class="manual-note">
            <b>Prayer Times:</b> BayanDigital pulls prayer times from the JAKIM e-Solat API. Prayer times are automatically updated and cached for each masjid based on their zone.
        </div>
    </div>
</section>

<section class="panel manual-section" id="screen-content">
    <div class="panel-head"><h2>5. Screen Content</h2></div>
    <div style="padding:20px;">
        <p>Screen content is what gets displayed on your TV screens. Each masjid can have multiple content items.</p>

        <h3>Content Types</h3>
        <ul>
            <li><strong>Announcements</strong> &mdash; Text-based messages for your congregation.</li>
            <li><strong>Images</strong> &mdash; Upload images to display on the screen (e.g. event flyers, banners).</li>
            <li><strong>Videos</strong> &mdash; Display video content on the TV screen.</li>
            <li><strong>QR Codes</strong> &mdash; Generate QR codes for donations, event registrations, or links.</li>
        </ul>

        <h3>Managing Content</h3>
        <ol class="manual-steps">
            <li>Navigate to <strong>Masjids</strong> and click <strong>Manage</strong> on your masjid.</li>
            <li>Click the <strong>Content</strong> tab or link to view all screen content items.</li>
            <li>Click <strong>Add Content</strong> to create a new item.</li>
            <li>Fill in the <strong>title</strong>, <strong>type</strong>, and <strong>body/upload</strong> fields.</li>
            <li>Toggle <strong>Active</strong> to control whether the item is currently shown on screen.</li>
            <li>Use <strong>Edit</strong> or <strong>Delete</strong> to modify or remove content items.</li>
        </ol>

        <div class="manual-note">
            <b>Tip:</b> Only active content items are displayed on TV screens. You can create content ahead of time and activate it when needed.
        </div>
    </div>
</section>

<section class="panel manual-section" id="tv-devices">
    <div class="panel-head"><h2>6. TV Device Setup</h2></div>
    <div style="padding:20px;">
        <p>The BayanDigital Android TV app connects to your masjid account to display content and prayer times.</p>

        <h3>Installing the App</h3>
        <ul>
            <li>Download the APK from the <strong>BayanDigital website</strong> or the <strong>Android TV app store</strong>.</li>
            <li>Install the APK on your <strong>Android TV</strong>, <strong>Android TV box</strong>, or <strong>Fire TV Stick</strong>.</li>
        </ul>

        <h3>Pairing a Device</h3>
        <ol class="manual-steps">
            <li>Open the BayanDigital app on your TV.</li>
            <li>The app will display a <strong>6-digit pairing code</strong>.</li>
            <li>In the admin panel, go to <strong>Masjids</strong> &rarr; <strong>Manage</strong> &rarr; <strong>Devices</strong>.</li>
            <li>Enter the 6-digit code and click <strong>Approve</strong>.</li>
            <li>The TV screen will start displaying your masjid's content and prayer times.</li>
        </ol>

        <h3>Managing Devices</h3>
        <ul>
            <li>View all paired devices in the <strong>Devices</strong> section for each masjid.</li>
            <li><strong>Approve</strong> or <strong>Reject</strong> pending device requests.</li>
            <li><strong>Revoke</strong> a device to disconnect it from your masjid.</li>
        </ul>

        <div class="manual-note">
            <b>Security:</b> Each device must be approved before it can display your content. This ensures only authorised TVs show your masjid's information.
        </div>
    </div>
</section>

<section class="panel manual-section" id="users">
    <div class="panel-head"><h2>7. User Management</h2></div>
    <div style="padding:20px;">
        <p>Admins can create and manage user accounts for their team.</p>

        <h3>User Roles</h3>
        <ul>
            <li><strong>Admin</strong> &mdash; Full access to all features including user management, backups, and system settings.</li>
            <li><strong>Operator</strong> &mdash; Can manage masjid settings and screen content only.</li>
        </ul>

        <h3>Managing Users</h3>
        <ul>
            <li>Go to <strong>Users</strong> in the sidebar to view all accounts.</li>
            <li>Click <strong>Add User</strong> to create a new account.</li>
            <li>Set the user's <strong>name</strong>, <strong>email</strong>, <strong>password</strong>, and <strong>role</strong>.</li>
            <li>Toggle <strong>Active</strong> status to enable or disable accounts.</li>
            <li>Use <strong>Edit</strong> or <strong>Delete</strong> to modify or remove users.</li>
        </ul>

        <div class="manual-note">
            <b>Note:</b> Only administrators can create, edit, or delete user accounts. Operators do not have access to the Users section.
        </div>
    </div>
</section>

<section class="panel manual-section" id="backups">
    <div class="panel-head"><h2>8. Backups</h2></div>
    <div style="padding:20px;">
        <p>BayanDigital supports automatic database backups to <strong>Google Drive</strong>.</p>

        <h3>Connecting Google Drive</h3>
        <ol class="manual-steps">
            <li>Go to <strong>Backups</strong> in the sidebar.</li>
            <li>Click <strong>Connect Google Drive</strong>.</li>
            <li>Sign in with your Google account and grant access.</li>
            <li>Backups will now be uploaded to your Google Drive automatically.</li>
        </ol>

        <h3>Running Backups</h3>
        <ul>
            <li><strong>Automatic:</strong> Backups run daily at <code>03:00</code> (Asia/Kuala_Lumpur).</li>
            <li><strong>Manual:</strong> Click <strong>Run Backup</strong> on the Backups page to trigger an immediate backup.</li>
        </ul>

        <h3>Managing Backups</h3>
        <ul>
            <li>View a list of all past backups with <strong>date</strong>, <strong>size</strong>, and <strong>status</strong>.</li>
            <li><strong>Download</strong> or <strong>Delete</strong> individual backups.</li>
            <li><strong>Prune Old Backups</strong> to remove backups older than 30 days.</li>
            <li>Click <strong>Disconnect</strong> to revoke Google Drive access at any time.</li>
        </ul>

        <div class="manual-note">
            <b>Note:</b> Backups are stored in a folder named <code>BayanDigital Backups</code> in your Google Drive. Old backups are automatically pruned after 30 days to save space.
        </div>
    </div>
</section>

<section class="panel manual-section" id="support">
    <div class="panel-head"><h2>9. Support &amp; Contact</h2></div>
    <div style="padding:20px;">
        <p>Need help? We're here for you. Reach out through any of the channels below.</p>

        <div class="manual-support">
            <a href="mailto:support@rarecreation.xyz">
                <span class="icon mail">&#9993;</span>
                <div>
                    <div>Email Support</div>
                    <small>support@rarecreation.xyz</small>
                </div>
            </a>
            <a href="https://buymeacoffee.com/rarecreation" target="_blank" rel="noopener">
                <span class="icon coffee">&#9749;</span>
                <div>
                    <div>Support Me</div>
                    <small>Buy Me a Coffee</small>
                </div>
            </a>
        </div>

        <h3 style="margin-top:24px;">Frequently Asked Questions</h3>

        <h3>Q: How do I reset my password?</h3>
        <p>Contact your administrator to reset your password, or use the <strong>Forgot Password</strong> link on the login page.</p>

        <h3>Q: Why is my TV not showing content?</h3>
        <p>Make sure the device has been <strong>approved</strong> in the admin panel under <strong>Masjids &rarr; Manage &rarr; Devices</strong>. Also check that you have <strong>active content</strong> items for your masjid.</p>

        <h3>Q: How do I change the prayer time source?</h3>
        <p>Go to <strong>Masjids &rarr; Manage</strong> and update the <strong>prayer time zone</strong> setting. BayanDigital uses JAKIM e-Solat data for all Malaysian zones.</p>

        <h3>Q: Can I use BayanDigital outside Malaysia?</h3>
        <p>Currently, BayanDigital is optimised for Malaysian masjids using JAKIM prayer time data. Support for other regions may be added in the future.</p>

        <h3>Q: How do I report a bug?</h3>
        <p>Email us at <a href="mailto:support@rarecreation.xyz"><strong>support@rarecreation.xyz</strong></a> with a description of the issue, and we'll get back to you as soon as possible.</p>

        <div class="manual-note">
            <b>Open Source:</b> BayanDigital is open source software. Report issues or contribute on <a href="https://github.com/hafriz/BayanDigital" target="_blank" rel="noopener" style="color:var(--emerald);font-weight:700;">GitHub</a>.
        </div>
    </div>
</section>

@endsection
