@extends('layouts.admin.app')

@section('title', translate('messages.Add new category'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/category.png')}}" class="w--20" alt="">
                </span>
                <span>
                    {{translate('messages.Add new category')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.beautybooking.category.store') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @if($language)
                        <ul class="nav nav-tabs mb-4 border-0">
                            @foreach ($language as $lang)
                                <li class="nav-item {{$lang == 'en' ? 'active' : ''}}">
                                    <a class="nav-link lang_link"
                                        href="#"
                                        id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            @if ($language)
                                @foreach($language as $key=> $lang)
                                    <div class="form-group {{$lang == 'en' ? '' : 'd-none'}} lang_form" id="{{$lang}}-form">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.name')}} ({{strtoupper($lang)}})</label>
                                        <input type="text" name="name[]"  value="{{ old('name.'.$key+1) }}" class="form-control" placeholder="{{translate('messages.new_category')}}" maxlength="191">
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                @endforeach
                            @else
                                <div class="form-group">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.name')}}</label>
                                    <input type="text" name="name" class="form-control" placeholder="{{translate('messages.new_category')}}" value="{{old('name')}}" maxlength="191">
                                </div>
                            @endif

                            <div class="form-group">
                                <label class="input-label">{{ translate('messages.Parent_Category') }} <small class="text-muted">({{ translate('messages.Optional') }})</small></label>
                                <select name="parent_id" class="form-control">
                                    <option value="">{{ translate('messages.No_Parent_Main_Category') }}</option>
                                    @php
                                        $allCategories = \Modules\BeautyBooking\Entities\BeautyServiceCategory::whereNull('parent_id')->get();
                                    @endphp
                                    @foreach($allCategories as $parentCategory)
                                        <option value="{{ $parentCategory->id }}">{{ $parentCategory->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="input-label">{{ translate('messages.Sort_Order') }}</label>
                                <input type="number" name="sort_order" class="form-control" value="0" min="0">
                            </div>

                            <div class="form-group">
                                <label class="input-label">{{ translate('messages.Status') }}</label>
                                <select name="status" class="form-control">
                                    <option value="1" selected>{{ translate('messages.Active') }}</option>
                                    <option value="0">{{ translate('messages.Inactive') }}</option>
                                </select>
                            </div>

                            <input name="position" value="0" class="initial-hidden">
                        </div>
                        <div class="col-md-6">
                            <div class="h-100 d-flex align-items-center flex-column">
                                <label class="mb-3 text-center">{{translate('messages.image')}} <small class="text-danger">* ( {{translate('messages.ratio')}} 3:2)</small></label>
                                <label class="text-center my-auto position-relative d-inline-block">
                                    <img class="img--176 border" id="viewer"
                                        src="{{asset('public/assets/admin/img/upload-img.png')}}"
                                        alt="image"/>
                                    <div class="icon-file-group">
                                        <div class="icon-file">
                                            <input type="file" name="image" id="customFileEg1" class="custom-file-input read-url"
                                                accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                                                <i class="tio-edit"></i>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-3">
                        <a href="{{ route('admin.beautybooking.category.list') }}" class="btn btn--reset">{{translate('messages.cancel')}}</a>
                        <button type="submit" class="btn btn--primary">{{translate('messages.add')}}</button>
                    </div>

                </form>
            </div>
        </div>

    </div>

@endsection

@push('script_2')
    <script src="{{asset('public/assets/admin/js/view-pages/category-index.js')}}"></script>
@endpush

