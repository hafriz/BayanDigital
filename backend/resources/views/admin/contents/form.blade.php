@extends('admin.layout')
@section('title', $content->exists ? 'Edit TV content' : 'Add TV content')
@section('subtitle', $masjid->name.' · '.$masjid->public_id)
@section('content')
<section class="panel"><div class="panel-body">
    <form method="POST" action="{{ $content->exists ? route('admin.masjids.contents.update', [$masjid, $content]) : route('admin.masjids.contents.store', $masjid) }}">
        @csrf @if($content->exists) @method('PUT') @endif
        <div class="form-grid">
            <div class="field"><label for="type">Content type</label><select id="type" name="type" required>@foreach(['announcement'=>'Announcement','schedule'=>'Ustaz schedule','ticker'=>'Running ticker','slide'=>'Slide','image'=>'Image'] as $value => $label)<option value="{{ $value }}" @selected(old('type', $content->type ?: 'announcement') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="field"><label for="title">Title</label><input id="title" name="title" value="{{ old('title', $content->title) }}" maxlength="150"></div>
            <div class="field full"><label for="body">Message / description</label><textarea id="body" name="body" maxlength="2000">{{ old('body', $content->body) }}</textarea></div>
            <div class="field full"><label for="media_path">Media URL or path</label><input id="media_path" name="media_path" value="{{ old('media_path', $content->media_path) }}" placeholder="https://…"><small>Used for image and slide content.</small></div>
            <div class="field"><label for="starts_at">Starts at</label><input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', $content->starts_at?->format('Y-m-d\TH:i')) }}"></div>
            <div class="field"><label for="ends_at">Ends at</label><input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', $content->ends_at?->format('Y-m-d\TH:i')) }}"></div>
            <div class="field"><label for="is_active">Display status</label><select id="is_active" name="is_active"><option value="1" @selected((string) old('is_active', $content->exists ? (int)$content->is_active : 1) === '1')>Active</option><option value="0" @selected((string) old('is_active', $content->exists ? (int)$content->is_active : 1) === '0')>Disabled</option></select></div>
            <div class="field"><label for="sort_order">Display order</label><input id="sort_order" name="sort_order" type="number" min="0" max="10000" value="{{ old('sort_order', $content->sort_order ?? 0) }}" required><small>Lower numbers appear first.</small></div>
        </div>
        <div class="form-actions"><button class="button" type="submit">{{ $content->exists ? 'Save content' : 'Add to screen' }}</button><a class="button secondary" href="{{ route('admin.masjids.contents.index', $masjid) }}">Cancel</a></div>
    </form>
</div></section>
@endsection
