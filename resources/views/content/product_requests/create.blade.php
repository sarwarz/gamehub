@extends('layouts.app')
@section('title', 'Create Product Request')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce">

    <form method="POST" action="{{ route('product-requests.store') }}">
        @csrf

        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
            <div class="d-flex flex-column justify-content-center">
                <h4 class="mb-1">Request a Product</h4>
                <p class="mb-0">Submit a product request for review</p>
            </div>
            <div class="d-flex gap-3">
                <a href="{{ route('product-requests.index') }}" class="btn btn-label-secondary">
                    Discard
                </a>
                <button type="submit" class="btn btn-primary">
                    Submit Request
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
                                   value="{{ old('title') }}"
                                   placeholder="e.g. Windows 11 Pro License"
                                   required>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="4"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Describe the product you are requesting">{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Source URL -->
                        <div class="mb-4">
                            <label class="form-label">Source URL</label>
                            <input type="url" name="source_url"
                                   class="form-control @error('source_url') is-invalid @enderror"
                                   value="{{ old('source_url') }}"
                                   placeholder="https://example.com/product-page">
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
                                    <option value="">-- Select Category --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
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
                                    <option value="">-- Select Platform --</option>
                                    @foreach($platforms as $p)
                                        <option value="{{ $p->id }}" {{ old('platform_id') == $p->id ? 'selected' : '' }}>
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
                                    <option value="">-- Select Type --</option>
                                    @foreach($types as $t)
                                        <option value="{{ $t->id }}" {{ old('type_id') == $t->id ? 'selected' : '' }}>
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
                                    <option value="">-- Select Region --</option>
                                    @foreach($regions as $r)
                                        <option value="{{ $r->id }}" {{ old('region_id') == $r->id ? 'selected' : '' }}>
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
                                    <option value="">-- Select Language --</option>
                                    @foreach($languages as $lang)
                                        <option value="{{ $lang->id }}" {{ old('language_id') == $lang->id ? 'selected' : '' }}>
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
                                    <option value="">-- Select OS / Device --</option>
                                    @foreach($workson as $w)
                                        <option value="{{ $w->id }}" {{ old('works_on_id') == $w->id ? 'selected' : '' }}>
                                            {{ $w->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('works_on_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Status (Admin Use) -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Request Status</h5>
                    </div>
                    <div class="card-body">
                        <select name="status"
                                class="form-select @error('status') is-invalid @enderror">
                            <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>
                                Pending
                            </option>
                            <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>
                                Approved
                            </option>
                            <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>
                                Rejected
                            </option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>
                                Completed
                            </option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

            </div>
        </div>

    </form>
</div>
@endsection
