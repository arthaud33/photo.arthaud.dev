@if(session('status'))
<div class="alert alert-{{session('status')}} alert-dismissible fade show my-3" role="alert">
    {{ session('content') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif