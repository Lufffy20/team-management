<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->

        <!-- Sidebar -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  <div class="app-brand demo">
    <a href="<?= \yii\helpers\Url::to(['site/index']) ?>" class="app-brand-link">
      <span class="app-brand-text demo menu-text fw-bolder ms-2">TeamTasks</span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
      <i class="bx bx-chevron-left bx-sm align-middle"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">

    <!-- Dashboard -->
    <li class="menu-item <?= Yii::$app->controller->id=='site' ? 'active' : '' ?>">
      <a href="<?= \yii\helpers\Url::to(['site/index']) ?>" class="menu-link">
        <i class="menu-icon tf-icons bx bx-home"></i>
        <div>Dashboard</div>
      </a>
    </li>

    <!-- All Tasks -->
    <li class="menu-item <?= Yii::$app->controller->id=='task' ? 'active' : '' ?>">
      <a href="<?= \yii\helpers\Url::to(['task/index']) ?>" class="menu-link">
        <i class="menu-icon bx bx-list-ul"></i>
        <div>All Tasks</div>
      </a>
    </li>

    <!-- Projects -->
    <li class="menu-item <?= Yii::$app->controller->id=='board' ? 'active' : '' ?>">
      <a href="<?= \yii\helpers\Url::to(['board/index']) ?>" class="menu-link">
        <i class="menu-icon bx bx-folder"></i>
        <div>Projects</div>
      </a>
    </li>

    <!-- Team -->
    <li class="menu-item 
      <?= (Yii::$app->controller->id=='team' || Yii::$app->controller->id=='teammembers') ? 'active open' : '' ?>">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon bx bx-group"></i>
        <div>Team</div>
      </a>

      <ul class="menu-sub">

        <li class="menu-item <?= Yii::$app->controller->id=='team' && Yii::$app->controller->action->id=='index' ? 'active' : '' ?>">
          <a href="<?= \yii\helpers\Url::to(['team/index']) ?>" class="menu-link">
            <div>All Teams</div>
          </a>
        </li>

        <li class="menu-item <?= Yii::$app->controller->id=='team' && Yii::$app->controller->action->id=='create' ? 'active' : '' ?>">
          <a href="<?= \yii\helpers\Url::to(['team/create']) ?>" class="menu-link">
            <div>Add Team</div>
          </a>
        </li>

        <li class="menu-item <?= Yii::$app->controller->id=='teammembers' ? 'active' : '' ?>">
          <a href="<?= \yii\helpers\Url::to(['teammembers/index']) ?>" class="menu-link">
            <div>Team Members</div>
          </a>
        </li>

      </ul>
    </li>

    <!-- Activity Log -->
    <li class="menu-item <?= in_array(Yii::$app->controller->id, ['activity-log','activity']) ? 'active' : '' ?>">
      <a href="<?= \yii\helpers\Url::to(['/activity-log/index']) ?>" class="menu-link">
        <i class="menu-icon bx bx-time"></i>
        <div>Activity Log</div>
      </a>
    </li>

    <!-- Profile -->
    <li class="menu-item <?= Yii::$app->controller->id=='user' 
    && Yii::$app->controller->action->id=='profile' ? 'active' : '' ?>">
  <a href="<?= \yii\helpers\Url::to(['user/profile']) ?>" class="menu-link">
    <i class="menu-icon bx bx-user"></i>
    <div>My Profile</div>
  </a>
</li>


    <!-- Admin Only -->
    <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->role == 1): ?>
      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Admin</span>
      </li>

      <li class="menu-item <?= Yii::$app->controller->id=='user' 
    && Yii::$app->controller->action->id=='index' ? 'active' : '' ?>">
  <a href="<?= \yii\helpers\Url::to(['user/index']) ?>" class="menu-link">
    <i class="menu-icon bx bx-shield-quarter"></i>
    <div>User Management</div>
  </a>
</li>

    <?php endif; ?>

  </ul>
</aside>
