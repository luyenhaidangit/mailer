@extends('layout.master')

@push('before-styles')
{{ style(mix('css/dropzone.css')) }}
{{ style('vendor/summernote/summernote-bs4.css') }}
@endpush

@section('master')
<div class="container-scroller">
    
    <div class="container-fluid page-body-wrapper">
        <div class="main-panel mr-0 mx-auto" >
            @yield('contents')
        </div>
    </div>
</div>
@endsection

@push('before-scripts')
<script>
    window.brand = <?php echo brand() ?>
</script>

@endpush