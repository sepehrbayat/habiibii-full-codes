@extends('layouts.admin.app')

@section('title', translate('messages.Update Staff'))


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
                        <span>{{ translate('messages.Update Staff') }}
                    </h1>
                    <p class="text-muted">{{ translate('Salon') }}: {{ $staff->salon->store->name ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <form action="{{ route('admin.beautybooking.staff.update', $staff->id) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-lg-12">
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="text-title mb-1">
                                {{ translate('messages.User_Info') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="input-label" for="">{{ translate('messages.name') }}</label>
                                        <input type="text" name="name" id="" class="form-control"
                                               value="{{ $staff->name }}"
                                               placeholder="{{ translate('messages.Type_staff_name') }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="input-label" for="">{{ translate('messages.email') }}</label>
                                        <input type="email" name="email" id="" class="form-control"
                                               value="{{ $staff->email }}"
                                               placeholder="{{ translate('messages.Type_email_address') }}">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="phone">{{ translate('messages.phone') }}</label>
                                        <input type="tel" id="phone" name="phone" class="form-control"
                                               placeholder="{{ translate('messages.Ex:') }} 017********" value="{{ $staff->phone }}">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="status">{{ translate('messages.status') }}</label>
                                        <select name="status" id="status" class="form-control">
                                            <option value="1" {{ $staff->status ? 'selected' : '' }}>{{ translate('messages.Active') }}</option>
                                            <option value="0" {{ !$staff->status ? 'selected' : '' }}>{{ translate('messages.Inactive') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="text-center">
                                        <label class="text--title fs-16 font-semibold mb-1">
                                            {{ translate('Profile_Image') }}
                                        </label>
                                        <div class="mb-20">
                                            <p class="fs-12">
                                                {{ translate('JPG, JPEG, PNG Less Than 1MB') }} <strong class="font-semibold">({{ translate('Ratio 1:1') }})</strong>
                                            </p>
                                        </div>
                                        <div class="upload-file image-general d-inline-block w-auto">
                                            <a href="javascript:void(0);" class="remove-btn opacity-0 z-index-99">
                                                <i class="tio-clear"></i>
                                            </a>
                                            <input type="file" name="avatar" class="upload-file__input single_file_input"
                                                accept=".webp, .jpg, .jpeg, .png" value="{{ $staff->avatar_full_url ?? '' }}">
                                            <label class="upload-file-wrapper w--180px">
                                                <div class="upload-file-textbox text-center">
                                                    <img width="34" height="34" src="{{ asset('public/assets/admin/img/document-upload.svg') }}" alt="">
                                                    <h6 class="mt-2 font-semibold text-center">
                                                        <span>{{ translate('Click to upload') }}</span>
                                                        <br>
                                                        {{ translate('or drag and drop') }}
                                                    </h6>
                                                </div>
                                                <img class="upload-file-img d-none" data-file-name="{{ $staff->avatar_full_url ?? '' }}" height="180" width="180" loading="lazy" src="{{ $staff->avatar_full_url ?? asset('public/assets/admin/img/100x100/1.png') }}" alt="">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="text-title mb-1">
                                {{ translate('messages.Specializations') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-0">
                                <label class="input-label" for="specializations">{{ translate('messages.Specializations') }}</label>
                                <input type="text" name="specializations_text" id="specializations" class="form-control"
                                       value="{{ is_array($staff->specializations) ? implode(', ', $staff->specializations) : '' }}"
                                       placeholder="{{ translate('e.g., Haircut, Coloring, Facial') }}">
                                <small class="text-muted">{{ translate('Comma separated values') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-12">
                    <div class="btn--container justify-content-end mt-3">
                        <button type="reset" id="reset_btn" data-file-name="{{ $staff->avatar_full_url ?? '' }}"
                                class="btn btn--reset min-w-120px shadow-none">{{ translate('messages.reset') }}</button>
                        <button type="submit"
                                class="btn btn--primary min-w-120px shadow-none">{{ translate('messages.submit') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <input type="hidden" id="file_size_error_text" value="{{ translate('file_size_too_big') }}">
    <input type="hidden" id="file_type_error_text" value="{{ translate('please_only_input_png_or_jpg_type_file') }}">
@endsection

@push('script_2')
    <script src="{{ asset('public/assets/admin/js/spartan-multi-image-picker.js') }}"></script>
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const specializationsInput = document.querySelector('input[name="specializations_text"]');
            if (specializationsInput && specializationsInput.value.trim()) {
                const specializations = specializationsInput.value.split(',').map(s => s.trim()).filter(s => s);
                specializations.forEach((spec, index) => {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = `specializations[${index}]`;
                    hiddenInput.value = spec;
                    this.appendChild(hiddenInput);
                });
            }
        });
    </script>
@endpush

