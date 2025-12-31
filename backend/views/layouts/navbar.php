

    <?php

use yii\helpers\Html;
use backend\assets\AppAsset;
use yii\helpers\Url;
$user = Yii::$app->user->identity;

AppAsset::register($this);
?>
<!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->

          <nav
            class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
            id="layout-navbar"
          >
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
              <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                <i class="bx bx-menu bx-sm"></i>
              </a>
            </div>

            <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
              <!-- Search -->
              <div class="navbar-nav align-items-center">
                <div class="nav-item d-flex align-items-center">
                  <i class="bx bx-search fs-4 lh-0"></i>
                  <input
                    type="text"
                    class="form-control border-0 shadow-none"
                    placeholder="Search..."
                    aria-label="Search..."
                  />
                </div>
              </div>
              <!-- /Search -->

              <ul class="navbar-nav flex-row align-items-center ms-auto">
                <!-- Place this tag where you want the button to render. -->
                <li class="nav-item lh-1 me-3">
                  <a
                    class="github-button"
                    href="https://github.com/themeselection/sneat-html-admin-template-free"
                    data-icon="octicon-star"
                    data-size="large"
                    data-show-count="true"
                    aria-label="Star themeselection/sneat-html-admin-template-free on GitHub"
                    >Star</a
                  >
                </li>

              <!-- User Dropdown -->
<li class="nav-item navbar-dropdown dropdown-user dropdown">
  <a class="nav-link dropdown-toggle hide-arrow" data-bs-toggle="dropdown" href="#">
    <div class="avatar avatar-online">
    <img
        src="<?= Yii::$app->avatar->get($user) ?>"
        alt="User Avatar"
        class="rounded-circle shadow-sm"
        width="40"
        height="40"
        style="object-fit: cover; object-position: center;"
    >
</div>


  </a>

  <!-- Dropdown Menu -->
  <ul class="dropdown-menu dropdown-menu-end">

    <!-- USER INFO SECTION -->
    <li>
      <a class="dropdown-item" href="#">
        <div class="d-flex">

          <div class="flex-shrink-0 me-3">
            <div class="avatar avatar-online">
              <img 
                src="<?= $user->avatar 
                      ? Yii::$app->request->baseUrl . '/uploads/avatars/' . $user->avatar
                      : 'https://ui-avatars.com/api/?name=' . urlencode($user->first_name) ?>"
                alt="User Avatar"
                class="rounded-circle"
                style="width:40px; height:40px; object-fit:cover; object-position:center;"
              >
            </div>
          </div>

          <div class="flex-grow-1">
            <span class="fw-semibold d-block">
              <?= Html::encode($user->first_name . " " . $user->last_name) ?>
            </span>
            <small class="text-muted">
              <?= $user->role == 1 ? 'Admin' : 'User' ?>
            </small>
          </div>

        </div>
      </a>
    </li>

    <li><div class="dropdown-divider"></div></li>

    <!-- My Profile -->
    <li>
      <a class="dropdown-item" href="<?= Url::to(['/user/profile']) ?>">
        <i class="bx bx-user me-2"></i>
        <span class="align-middle">My Profile</span>
      </a>
    </li>

    <!-- Settings -->
    <li>
      <a class="dropdown-item" href="#">
        <i class="bx bx-cog me-2"></i>
        <span class="align-middle">Settings</span>
      </a>
    </li>

    <!-- Billing -->
    <li>
      <a class="dropdown-item" href="#">
        <span class="d-flex align-items-center">
          <i class="bx bx-credit-card me-2"></i>
          <span class="flex-grow-1">Billing</span>
          <span class="badge bg-danger rounded-pill">4</span>
        </span>
      </a>
    </li>

    <li><div class="dropdown-divider"></div></li>

    <!-- Hidden logout for tests -->
    <li style="display:none;">
      <a href="<?= Url::to(['site/logout']) ?>" data-method="post">Logout</a>
    </li>

    <!-- Logout Button -->
    <li>
      <?= Html::beginForm(['/site/logout'], 'post')
        . Html::submitButton(
            '<i class="bx bx-power-off me-2"></i> 
            <span class="align-middle">Log Out</span>',
            [
              'class' => 'dropdown-item',
              'style' => 'border:none;background:none;cursor:pointer;'
            ]
        )
        . Html::endForm();
      ?>
    </li>

  </ul>
</li>
                <!--/ User -->
              </ul>
            </div>
          </nav>