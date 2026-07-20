<div class="modal fade" id="{{ $modalId }}" tabindex="-1">

    <div class="modal-dialog modal-xl">

        <form method="POST" action="{{ $action }}">

            @csrf

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title">
                        {{ $title }}
                    </h5>

                    <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>

                </div>


                <div class="modal-body">

                    {{ $slot }}

                </div>


                <div class="modal-footer">

                    <button type="submit"
                        class="btn btn-primary">

                        Simpan

                    </button>

                </div>


            </div>

        </form>

    </div>

</div>