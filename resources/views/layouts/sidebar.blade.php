<div id="sidebar"
     class="bg-primary text-white d-flex flex-column justify-content-between sidebar-expanded">

    <div>

        {{-- HEADER --}}
        <div class="p-3 d-flex justify-content-between align-items-center">

            <h4 class="fw-bold mb-0 logo-text">
                <a href="/" class="text-decoration-none text-white">
                    KonveksiApp
                </a>
            </h4>

            <button class="btn btn-sm btn-light"
                    id="toggleSidebar">
                <i class="bi bi-list"></i>
            </button>

        </div>

        {{-- MENU --}}
        <div class="px-2">

            {{-- DATA MASTER --}}
            <div class="mb-4">

                <small class="text-white-50 fw-bold menu-title">
                    DATA MASTER
                </small>

                <ul class="nav flex-column mt-2">

                    <li class="nav-item">
                        <a href="/workers" class="nav-link text-white">
                            <i class="bi bi-people me-2"></i>
                            <span class="menu-text">Maklun</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/courier" class="nav-link text-white">
                            <i class="bi bi-truck me-2"></i>
                            <span class="menu-text">Kurir</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/product" class="nav-link text-white">
                            <i class="bi bi-box me-2"></i>
                            <span class="menu-text">Produk</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/variant" class="nav-link text-white">
                            <i class="bi bi-grid me-2"></i>
                            <span class="menu-text">Varian</span>
                        </a>
                    </li>

                </ul>
            </div>


            {{-- GUDANG --}}
            <div class="mb-4">

                <small class="text-white-50 fw-bold menu-title">
                    GUDANG
                </small>

                <ul class="nav flex-column mt-2">

                    <li class="nav-item">
                        <a href="/raw_material_master" class="nav-link text-white">
                            <i class="bi bi-box-seam me-2"></i>
                            <span class="menu-text">Bahan Baku</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/inventory-product" class="nav-link text-white">
                            <i class="bi bi-bag-check me-2"></i>
                            <span class="menu-text">Stok Produk</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/production_inventory" class="nav-link text-white">
                            <i class="bi bi-clipboard-data me-2"></i>
                            <span class="menu-text">Stok Opname</span>
                        </a>
                    </li>

                </ul>
            </div>


            {{-- PRODUKSI --}}
            <div>

                <small class="text-white-50 fw-bold menu-title">
                    PRODUKSI
                </small>

                <ul class="nav flex-column mt-2">

                    <li class="nav-item">
                        <a href="/raw_material_stock_movement" class="nav-link text-white">
                            <i class="bi bi-arrow-down-square me-2"></i>
                            <span class="menu-text">Penerimaan Bahan</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/production_period" class="nav-link text-white">
                            <i class="bi bi-arrow-down-square me-2"></i>
                            <span class="menu-text">Penentuan Periode</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/production_planning" class="nav-link text-white">
                            <i class="bi bi-calendar-check me-2"></i>
                            <span class="menu-text">Perencanaan Produksi</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/rawMaterial" class="nav-link text-white">
                            <i class="bi bi-gear me-2"></i>
                            <span class="menu-text">Produksi Tahap 1</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/productionBatch" class="nav-link text-white">
                            <i class="bi bi-gear-wide-connected me-2"></i>
                            <span class="menu-text">Produksi Tahap 2</span>
                        </a>
                    </li>

                </ul>
            </div>

            {{-- <div>

                <small class="text-white-50 fw-bold menu-title">
                    LAPORAN
                </small>

                <ul class="nav flex-column mt-2">

                    <li class="nav-item">
                        <a href="/production_report" class="nav-link text-white">
                            <i class="bi bi-bar-chart me-2"></i>
                            <span class="menu-text">Laporan Produksi</span>
                        </a>
                    </li>

                </ul>
            </div> --}}

            {{-- KELUAR --}}
            <div class="mb-4">

                <small class="text-white-50 fw-bold menu-title">
                    KELUAR
                </small>

                <ul class="nav flex-column mt-2">

                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf

                            <button type="submit" class="nav-link text-white border-0 bg-transparent w-100 text-start">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                <span class="menu-text">Keluar</span>
                            </button>
                        </form>
                    </li>

                </ul>
            </div>

        </div>
    </div>


    {{-- FOOTER --}}
    <div class="p-3 border-top border-light border-opacity-25">
        <small class="text-white-50 menu-text">
            © {{ date('Y') }} Konveksi IS
        </small>
    </div>

</div>