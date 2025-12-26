<!-- Documents Tab -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title">{{ translate('messages.Documents') }}</h5>
    </div>
    <div class="card-body">
        @if($salon->documents && count($salon->documents) > 0)
            <div class="row">
                @foreach($salon->documents as $index => $document)
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                @php
                                    $extension = pathinfo($document, PATHINFO_EXTENSION);
                                    $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                @endphp
                                
                                @if($isImage)
                                    <a href="{{ asset('storage/' . $document) }}" target="_blank" class="d-block mb-2">
                                        <img src="{{ asset('storage/' . $document) }}" class="img-fluid rounded" alt="Document {{ $index + 1 }}" style="max-height: 200px;">
                                    </a>
                                @else
                                    <div class="mb-2">
                                        <i class="tio-file-text" style="font-size: 48px; color: #6c757d;"></i>
                                    </div>
                                @endif
                                
                                <a href="{{ asset('storage/' . $document) }}" target="_blank" class="btn btn-sm btn--primary" download>
                                    <i class="tio-download"></i> {{ translate('Download') }} {{ $index + 1 }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center text-muted py-5">
                <i class="tio-file-text" style="font-size: 64px; color: #ddd;"></i>
                <p class="mt-3">{{ translate('No documents uploaded') }}</p>
            </div>
        @endif
    </div>
</div>

