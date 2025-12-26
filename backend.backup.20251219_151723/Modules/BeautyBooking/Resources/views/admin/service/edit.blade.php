@extends('layouts.admin.app')

@section('title', translate('messages.Update Service'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header pb-20">
            <div class="d-flex justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="page-header-title text-break">
                        <span class="page-header-icon">
                            <img src="{{ asset('public/assets/admin/img/car-logo.png') }}" alt="">
                        </span>
                        <span>{{ translate('messages.Update Service') }}
                    </h1>
                    <p class="text-muted">{{ translate('Salon') }}: {{ $service->salon->store->name ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <form action="{{ route('admin.beautybooking.service.update', $service->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-lg-12">
                    <div class="card mt-4">
                        <div class="card-header">
                            <div>
                                <h5 class="text-title mb-1">
                                    {{ translate('messages.General_Information') }}
                                </h5>
                                <p class="fs-12 mb-0">
                                    {{ translate('messages.Insert the basic information of the service') }}
                                </p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="service_name">
                                            {{ translate('messages.Service_Name') }} <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="name" id="service_name" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               value="{{ old('name', $service->name) }}" 
                                               placeholder="{{ translate('messages.type_service_name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="choice_category">
                                            {{ translate('messages.Category') }} <span class="text-danger">*</span>
                                        </label>
                                        <select name="category_id" id="choice_category" 
                                                class="form-control js-select2-custom @error('category_id') is-invalid @enderror" 
                                                data-placeholder="{{ translate('messages.select_category') }}" required>
                                            <option value="" selected disabled>{{ translate('messages.select_category') }}</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" 
                                                        {{ old('category_id', $service->category_id) == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="service_description">
                                            {{ translate('messages.Description') }}
                                        </label>
                                        <textarea name="description" id="service_description" 
                                                  class="form-control @error('description') is-invalid @enderror" 
                                                  rows="3" 
                                                  placeholder="{{ translate('messages.type_service_description') }}">{{ old('description', $service->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h5 class="text-title mb-1">
                                    {{ translate('messages.Pricing_&_Duration') }}
                                </h5>
                                <p class="fs-12 mb-0">
                                    {{ translate('messages.Insert_The_Service\'s_Pricing_And_Duration') }}
                                </p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="duration_minutes">
                                            {{ translate('messages.Duration_(Minutes)') }} <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" name="duration_minutes" id="duration_minutes" 
                                               class="form-control @error('duration_minutes') is-invalid @enderror" 
                                               value="{{ old('duration_minutes', $service->duration_minutes) }}" 
                                               min="1" required>
                                        @error('duration_minutes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="service_price">
                                            {{ translate('messages.Price') }} <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" name="price" id="service_price" 
                                               class="form-control @error('price') is-invalid @enderror" 
                                               value="{{ old('price', $service->price) }}" 
                                               step="0.01" min="0" required>
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h5 class="text-title mb-1">
                                    {{ translate('messages.Image_&_Status') }}
                                </h5>
                                <p class="fs-12 mb-0">
                                    {{ translate('messages.Insert_The_Service\'s_Image_And_Status') }}
                                </p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label">{{ translate('messages.Service_Image') }}</label>
                                        @if($service->image)
                                            <div class="mb-2">
                                                <img src="{{ asset('storage/app/public/' . $service->image) }}" 
                                                     alt="{{ $service->name }}" 
                                                     style="max-width: 100px; max-height: 100px; border-radius: 5px;">
                                            </div>
                                        @endif
                                        <input type="file" name="image" 
                                               class="form-control @error('image') is-invalid @enderror" 
                                               accept="image/jpeg,image/png,image/jpg">
                                        <small class="text-muted">{{ translate('messages.Max_size:_2MB,_Formats:_JPEG,_PNG,_JPG') }}</small>
                                        @error('image')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="service_status">
                                            {{ translate('messages.Status') }}
                                        </label>
                                        <select name="status" id="service_status" class="form-control">
                                            <option value="1" {{ old('status', $service->status) ? 'selected' : '' }}>
                                                {{ translate('messages.Active') }}
                                            </option>
                                            <option value="0" {{ !old('status', $service->status) ? 'selected' : '' }}>
                                                {{ translate('messages.Inactive') }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="btn--container justify-content-end mt-3">
                        <button type="reset" class="btn btn--reset">{{ translate('messages.reset') }}</button>
                        <button type="submit" class="btn btn--primary">
                            <i class="tio-checkmark"></i> {{ translate('messages.Submit') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

