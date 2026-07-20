<form method="POST"
      action="{{ $action }}"
      class="d-inline delete-form">

@csrf

@method('DELETE')

<button type="button"
        class="btn btn-danger btn-sm btn-delete">

Hapus

</button>

</form>