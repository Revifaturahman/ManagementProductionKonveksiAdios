<div class="modal fade" id="{{ $modalId }}" tabindex="-1">

    <div class="modal-dialog modal-lg">

        <form method="POST" action="{{ $action }}">

            @csrf
            @method('PUT')

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
                        class="btn btn-success">

                        Update

                    </button>

                </div>


            </div>

        </form>

    </div>

</div>