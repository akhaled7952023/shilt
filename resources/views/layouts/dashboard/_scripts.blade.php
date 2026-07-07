<!-- BEGIN VENDOR JS-->
<script src="{{ asset('asset/dashboard') }}/vendors/js/vendors.min.js" type="text/javascript"></script>
<!-- BEGIN VENDOR JS-->
<!-- BEGIN PAGE VENDOR JS-->
<script src="{{ asset('asset/dashboard') }}/vendors/js/charts/chartist.min.js" type="text/javascript"></script>
<script src="{{ asset('asset/dashboard') }}/vendors/js/charts/chartist-plugin-tooltip.min.js" type="text/javascript">
</script>
<script src="{{ asset('asset/dashboard') }}/vendors/js/charts/raphael-min.js" type="text/javascript"></script>
<script src="{{ asset('asset/dashboard') }}/vendors/js/charts/morris.min.js" type="text/javascript"></script>
<script src="{{ asset('asset/dashboard') }}/vendors/js/timeline/horizontal-timeline.js" type="text/javascript"></script>
<!-- END PAGE VENDOR JS-->
<!-- BEGIN MODERN JS-->
<script src="{{ asset('asset/dashboard') }}/js/core/app-menu.js" type="text/javascript"></script>
<script src="{{ asset('asset/dashboard') }}/js/core/app.js" type="text/javascript"></script>
<script src="{{ asset('asset/dashboard') }}/js/scripts/customizer.js" type="text/javascript"></script>
<!-- END MODERN JS-->
<!-- BEGIN PAGE LEVEL JS-->
<script src="{{ asset('asset/dashboard') }}/js/scripts/pages/dashboard-ecommerce.js" type="text/javascript"></script>
<!-- END PAGE LEVEL JS-->
<!--Summer Note -->
<script src="{{ asset('asset/dashboard') }}/vendors/js/editors/summernote/summernote.js" type="text/javascript">
</script>
<script src="{{ asset('asset/dashboard') }}/js/scripts/editors/editor-summernote.js" type="text/javascript"></script>
<script src="{{ asset('asset/dashboard') }}/js/scripts/extensions/dropzone.js" type="text/javascript"></script>
<script src="{{ asset('asset/dashboard') }}/vendors/dropify/dropify.min.js"></script>
<script src="{{ asset('asset/dashboard') }}/vendors/js/tables/datatable/datatables.min.js" type="text/javascript"></script>
<script src="{{ asset('asset/dashboard') }}/js/scripts/tables/datatables/datatable-basic.js" type="text/javascript"></script>
<script src="{{asset('asset/dashboard')}}/vendors/js/editors/ckeditor/ckeditor.js" type="text/javascript"></script>
<script src="{{asset('asset/dashboard')}}/js/scripts/editors/editor-ckeditor.js" type="text/javascript"></script>
@stack('custom-js')

<script>
    $(document).ready(function() {
        $('.dropify').dropify({
            messages: {
                default: '{{ __('dashboard.default') }}',
                replace: '{{ __('dashboard.replace') }}',
                remove: '{{ __('dashboard.remove') }}',
                error: '{{ __('dashboard.error') }}',
            }
        });
    });
</script>
