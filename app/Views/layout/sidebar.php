<li class="nav-item">
    <a href="/" class="nav-link">
        <i class="nav-icon bi bi-palette"></i>
        <p>Dashboard</p>
    </a>
</li>
<li class="nav-item">
    <a href="<?= url_to('parties') ?>" class="nav-link">
        <i class="nav-icon bi bi-palette"></i>
        <p>Parties</p>
    </a>
</li>



<li class="nav-header">MULTI LEVEL EXAMPLE</li>
<li class="nav-item">
    <a href="#" class="nav-link">
        <i class="nav-icon bi bi-circle-fill"></i>
        <p>Level 1</p>
    </a>
</li>
<li class="nav-item">
    <a href="#" class="nav-link">
        <i class="nav-icon bi bi-circle-fill"></i>
        <p>
            Level 1
            <i class="nav-arrow bi bi-chevron-right"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="#" class="nav-link">
                <i class="nav-icon bi bi-circle"></i>
                <p>Level 2</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link">
                <i class="nav-icon bi bi-circle"></i>
                <p>
                    Level 2
                    <i class="nav-arrow bi bi-chevron-right"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-record-circle-fill"></i>
                        <p>Level 3</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-record-circle-fill"></i>
                        <p>Level 3</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-record-circle-fill"></i>
                        <p>Level 3</p>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link">
                <i class="nav-icon bi bi-circle"></i>
                <p>Level 2</p>
            </a>
        </li>
    </ul>
</li>
<li class="nav-item">
    <a href="#" class="nav-link">
        <i class="nav-icon bi bi-circle-fill"></i>
        <p>Level 1</p>
    </a>
</li>

<li class="nav-header">LABELS</li>
<li class="nav-item">
    <a href="#" class="nav-link">
        <i class="nav-icon bi bi-circle text-danger"></i>
        <p class="text">Important</p>
    </a>
</li>
<li class="nav-item">
    <a href="#" class="nav-link">
        <i class="nav-icon bi bi-circle text-warning"></i>
        <p>Warning</p>
    </a>
</li>
<li class="nav-item">
    <a href="#" class="nav-link">
        <i class="nav-icon bi bi-circle text-info"></i>
        <p>Informational</p>
    </a>
</li>