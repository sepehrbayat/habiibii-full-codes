@extends('layouts.admin.app')

@section('title', translate('Update Category'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/category.png')}}" class="w--22" alt="">
                </span>
                <span>
                    {{translate('messages.Add new category')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.beautybooking.category.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            @if($language)
                                <ul class="nav nav-tabs mb-4 border-0">
                                    @foreach ($language as $lang)
                                        <li class="nav-item {{$lang == $defaultLang ? 'active' : ''}}">
                                            <a class="nav-link lang_link" href="#" id="{{ $lang }}-link">
                                                {{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @if($language)
                                @foreach($language as $lang)
                                    <?php
                                    if(count($category?->translations ?? [])){
                                        $translate = [];
                                        foreach($category['translations'] as $t)
                                        {
                                            if($t->locale == $lang && $t->key=="name"){
                                                $translate[$lang]['name'] = $t->value;
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="form-group {{$lang == $defaultLang ? '' : 'd-none'}} lang_form" id="{{$lang}}-form">
                                        <label class="input-label">{{ translate('Category Name') }} ({{strtoupper($lang)}})</label>
                                        <input type="text" name="name[]" class="form-control" 
                                               placeholder="{{ translate('Category Name') }}" 
                                               maxlength="191" 
                                               value="{{ $translate[$lang]['name'] ?? ($lang == $defaultLang ? $category->getRawOriginal('name') : '') }}">
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                @endforeach
                            @else
                                <div class="form-group">
                                    <label class="input-label">{{ translate('Category Name') }}</label>
                                    <input type="text" name="name" class="form-control" 
                                           placeholder="{{ translate('Category Name') }}" 
                                           value="{{ $category->name }}" 
                                           maxlength="191">
                                </div>
                            @endif

                            <div class="form-group">
                                <label>{{ translate('Parent Category') }} <small class="text-muted">({{ translate('Optional') }})</small></label>
                                <select name="parent_id" class="form-control">
                                    <option value="">{{ translate('No Parent (Main Category)') }}</option>
                                    @php
                                        $allCategories = \Modules\BeautyBooking\Entities\BeautyServiceCategory::whereNull('parent_id')
                                            ->where('id', '!=', $category->id)
                                            ->get();
                                    @endphp
                                    @foreach($allCategories as $parentCategory)
                                        <option value="{{ $parentCategory->id }}" 
                                                {{ old('parent_id', $category->parent_id) == $parentCategory->id ? 'selected' : '' }}>
                                            {{ $parentCategory->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>{{ translate('Sort Order') }}</label>
                                <input type="number" name="sort_order" class="form-control" 
                                       value="{{ old('sort_order', $category->sort_order ?? 0) }}" 
                                       min="0">
                            </div>

                            <div class="form-group">
                                <label>{{ translate('Status') }}</label>
                                <select name="status" class="form-control">
                                    <option value="1" {{ old('status', $category->status) ? 'selected' : '' }}>
                                        {{ translate('Active') }}
                                    </option>
                                    <option value="0" {{ !old('status', $category->status) ? 'selected' : '' }}>
                                        {{ translate('Inactive') }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="h-100 d-flex align-items-center flex-column">
                                <label class="mb-4">{{ translate('Image') }}
                                    <small class="text-muted">({{ translate('Optional') }})</small>
                                </label>
                                <label class="text-center my-auto position-relative d-inline-block">
                                    @if($category->image)
                                        <img class="img--176 border" id="viewer"
                                             src="{{ asset('storage/app/public/' . $category->image) }}"
                                             data-onerror-image="{{ asset('public/assets/admin/img/upload-img.png') }}"
                                             alt=""/>
                                    @else
                                        <img class="img--176 border" id="viewer"
                                             src="{{ asset('public/assets/admin/img/upload-img.png') }}"
                                             alt=""/>
                                    @endif
                                    <div class="icon-file-group">
                                        <div class="icon-file">
                                            <input type="file" name="image" id="customFileEg1" class="custom-file-input read-url"
                                                   accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                            <i class="tio-edit"></i>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="btn--container justify-content-end mt-3">
                        <a href="{{ route('admin.beautybooking.category.list') }}" class="btn btn--reset">{{ translate('Cancel') }}</a>
                        <button type="submit" class="btn btn--primary">{{ translate('Update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
<script>
    "use strict";
    
    // Language tab switching
    $('.lang_link').on('click', function(e) {
        e.preventDefault();
        $('.lang_link').removeClass('active');
        $(this).addClass('active');
        $('.lang_form').addClass('d-none');
        $('#' + $(this).attr('id').replace('-link', '-form')).removeClass('d-none');
    });

    // Image preview
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#viewer').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    $("#customFileEg1").change(function() {
        readURL(this);
    });
</script>
@endpush

