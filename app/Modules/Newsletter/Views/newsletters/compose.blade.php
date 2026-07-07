@extends('admin.layouts.app')

@section('title', 'Send Newsletter')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Send Newsletter'],
    ]" />

    <x-admin.page-header title="Send Newsletter" />

    <div class="alert alert-info small">
        Batch sends are queued and delivered one email at a time through the mail driver configured under
        <strong>Settings → Email</strong>. A queue worker must be running, for example:
        <code>php artisan queue:work --queue=newsletters,default</code>
    </div>

    <form method="POST" action="{{ route('admin.newsletters.send') }}" class="card border-0 shadow-sm"
          onsubmit="return confirm('Queue this newsletter for all active subscribers of the selected list?');">
        @csrf
        <div class="card-body">
            <div class="mb-3">
                <label for="newsletter_list_id" class="form-label">Newsletter list</label>
                <select class="form-select @error('newsletter_list_id') is-invalid @enderror" id="newsletter_list_id" name="newsletter_list_id" required>
                    <option value="">Select list...</option>
                    @foreach ($lists as $list)
                        <option value="{{ $list->id }}" @selected(old('newsletter_list_id') == $list->id)>
                            {{ $list->name }} ({{ $subscriberCounts[$list->id] ?? 0 }} active)
                        </option>
                    @endforeach
                </select>
                @error('newsletter_list_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject"
                       value="{{ old('subject') }}" required>
                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <x-admin.form.rich-text
                label="Body"
                name="body"
                :value="old('body')"
            />
        </div>
        <div class="card-footer bg-white border-top d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="{{ admin_icon('bi-send') }} me-1"></i> Queue for subscribers
            </button>
            <a href="{{ route('admin.newsletter-sends.index') }}" class="btn btn-outline-secondary">Send history</a>
        </div>
    </form>
@endsection
