@extends('layouts.app')
@section('title', 'Edit Product Request')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce">

    <form method="POST" action="{{ route('product-requests.update', $request->id) }}">
        @csrf
        @method('PUT')

        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
            <div class="d-flex flex-column justify-content-center">
                <h4 class="mb-1">Edit Product Request</h4>
                <p class="mb-0">Update request details and status</p>
            </div>
            <div class="d-flex gap-3">
                <a href="{{ route('product-requests.index') }}" class="btn btn-label-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    Update Request
                </button>
            </div>
        </div>

        <!-- Single Column -->
        <div class="row">
            <div class="col-12">

                <!-- Basic Information -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Request Information</h5>
                    </div>
                    <div class="card-body">

                        <!-- Title -->
                        <div class="mb-4">
                            <label class="form-label">Product Title</label>
                            <input type="text" name="title"
                                   class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $request->title) }}"
                                   required>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="4"
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description', $request->description) }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Source URL -->
                        <div class="mb-4">
                            <label class="form-label">Source URL</label>
                            <input type="url" name="source_url"
                                   class="form-control @error('source_url') is-invalid @enderror"
                                   value="{{ old('source_url', $request->source_url) }}">
                            @error('source_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                    </div>
                </div>

                <!-- Classification -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Product Classification</h5>
                    </div>
                    <div class="card-body">

                        <div class="row g-4">

                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select name="category_id"
                                        class="form-select select2 @error('category_id') is-invalid @enderror" required>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}"
                                            {{ old('category_id', $request->category_id) == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Platform</label>
                                <select name="platform_id"
                                        class="form-select select2 @error('platform_id') is-invalid @enderror" required>
                                    @foreach($platforms as $p)
                                        <option value="{{ $p->id }}"
                                            {{ old('platform_id', $request->platform_id) == $p->id ? 'selected' : '' }}>
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('platform_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Product Type</label>
                                <select name="type_id"
                                        class="form-select select2 @error('type_id') is-invalid @enderror" required>
                                    @foreach($types as $t)
                                        <option value="{{ $t->id }}"
                                            {{ old('type_id', $request->type_id) == $t->id ? 'selected' : '' }}>
                                            {{ $t->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Region</label>
                                <select name="region_id"
                                        class="form-select select2 @error('region_id') is-invalid @enderror" required>
                                    @foreach($regions as $r)
                                        <option value="{{ $r->id }}"
                                            {{ old('region_id', $request->region_id) == $r->id ? 'selected' : '' }}>
                                            {{ $r->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('region_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Language</label>
                                <select name="language_id"
                                        class="form-select select2 @error('language_id') is-invalid @enderror" required>
                                    @foreach($languages as $lang)
                                        <option value="{{ $lang->id }}"
                                            {{ old('language_id', $request->language_id) == $lang->id ? 'selected' : '' }}>
                                            {{ $lang->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('language_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Works On</label>
                                <select name="works_on_id"
                                        class="form-select select2 @error('works_on_id') is-invalid @enderror" required>
                                    @foreach($workson as $w)
                                        <option value="{{ $w->id }}"
                                            {{ old('works_on_id', $request->works_on_id) == $w->id ? 'selected' : '' }}>
                                            {{ $w->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('works_on_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Request Status</h5>
                    </div>
                    <div class="card-body">
                        <select name="status"
                                class="form-select @error('status') is-invalid @enderror">
                            @foreach(['pending','approved','rejected','completed'] as $status)
                                <option value="{{ $status }}"
                                    {{ old('status', $request->status) === $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

            </div>
        </div>

    </form>
</div>
@endsection
