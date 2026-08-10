@extends('admin.layout')
@section('title', $notice->exists ? 'Edit global notice' : 'Publish global notice')
@section('subtitle', 'This message can appear on every Android screen and masjid or surau public page.')
@section('content')
<section class="panel"><div class="panel-body">
    <form method="POST" action="{{ $notice->exists ? route('admin.global-notices.update', $notice) : route('admin.global-notices.store') }}">
        @csrf @if($notice->exists) @method('PUT') @endif
        <div class="form-grid">
            <div class="field full"><label for="title">Notice heading</label><input id="title" name="title" value="{{ old('title', $notice->title) }}" maxlength="150" placeholder="Severe weather warning" required></div>
            <div class="field full"><label for="body">Notice details</label><textarea id="body" name="body" maxlength="2000" placeholder="Explain the affected area, timing, and action people should take." required>{{ old('body', $notice->body) }}</textarea></div>
            <div class="field"><label for="starts_at">Show from</label><input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', $notice->starts_at?->format('Y-m-d\TH:i')) }}"><small>Leave empty to show immediately.</small></div>
            <div class="field"><label for="ends_at">Remove automatically at</label><input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', $notice->ends_at?->format('Y-m-d\TH:i')) }}"><small>Leave empty to remove it manually.</small></div>
            <div class="field"><label for="is_active">Status</label><select id="is_active" name="is_active" required><option value="1" @selected((string) old('is_active', $notice->exists ? (int) $notice->is_active : 1) === '1')>Active</option><option value="0" @selected((string) old('is_active', $notice->exists ? (int) $notice->is_active : 1) === '0')>Disabled</option></select></div>
        </div>
        <div class="form-actions"><button class="button" type="submit">{{ $notice->exists ? 'Save notice' : 'Publish to all locations' }}</button><a class="button secondary" href="{{ route('admin.global-notices.index') }}">Cancel</a></div>
    </form>
</div></section>
@endsection
