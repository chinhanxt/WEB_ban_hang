<?php include 'app/views/shares/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="font-weight-bold mb-0" style="font-size: 1.5rem;">
        <i class="fas fa-users-cog mr-2 text-primary"></i>QUẢN LÝ NGƯỜI DÙNG
    </h2>
    <span class="badge badge-primary px-3 py-2"><?php echo count($accounts); ?> thành viên</span>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 15px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="bg-light text-muted small text-uppercase" style="font-size: 0.75rem;">
                    <tr>
                        <th class="border-0 px-4 py-3">ID</th>
                        <th class="border-0 py-3">Tên đăng nhập</th>
                        <th class="border-0 py-3">Email</th>
                        <th class="border-0 py-3 text-center">Trạng thái</th>
                        <th class="border-0 py-3 text-center">Vai trò</th>
                        <th class="border-0 px-4 py-3 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.9rem;">
                    <?php foreach ($accounts as $acc): ?>
                        <tr class="<?php echo !$acc->is_active ? 'bg-light' : ''; ?>">
                            <td class="px-4 py-3 text-muted font-weight-bold">#<?php echo $acc->id; ?></td>
                            <td class="py-3">
                                <div class="d-flex flex-column">
                                    <span class="font-weight-bold"><?php echo htmlspecialchars($acc->username); ?></span>
                                    <small class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($acc->fullname); ?></small>
                                </div>
                            </td>
                            <td class="py-3 text-muted small"><?php echo htmlspecialchars($acc->email); ?></td>
                            <td class="py-3 text-center">
                                <?php if ($acc->is_active): ?>
                                    <span class="badge badge-success-soft text-success px-2 py-1" style="font-size: 0.7rem; background: #e6fcf5;">
                                        <i class="fas fa-check-circle mr-1"></i>Hoạt động
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-danger-soft text-danger px-2 py-1" style="font-size: 0.7rem; background: #fff5f5;">
                                        <i class="fas fa-lock mr-1"></i>Đã khóa
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 text-center">
                                <span class="badge <?php echo $acc->role === 'admin' ? 'badge-danger' : 'badge-info'; ?> px-2 py-1" style="font-size: 0.7rem; border-radius: 6px;">
                                    <?php echo strtoupper($acc->role); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="d-flex justify-content-end align-items-center">
                                    <?php if ($acc->id !== $_SESSION['user']->id): ?>
                                        <a href="/webbanhang/AuthController/toggleStatus/<?php echo $acc->id; ?>/<?php echo $acc->is_active; ?>" 
                                           class="btn btn-sm <?php echo $acc->is_active ? 'btn-outline-danger' : 'btn-outline-success'; ?> rounded-circle mr-2"
                                           title="<?php echo $acc->is_active ? 'Khóa tài khoản' : 'Mở khóa'; ?>"
                                           style="width: 30px; height: 30px; padding: 0; line-height: 30px;">
                                            <i class="fas <?php echo $acc->is_active ? 'fa-user-slash' : 'fa-user-check'; ?> small"></i>
                                        </a>
                                        
                                        <button onclick="confirmReset(<?php echo $acc->id; ?>, '<?php echo $acc->username; ?>')" 
                                                class="btn btn-sm btn-outline-warning rounded-circle" 
                                                title="Reset mật khẩu"
                                                style="width: 30px; height: 30px; padding: 0; line-height: 30px;">
                                            <i class="fas fa-key small"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="badge badge-light text-muted" style="font-size: 0.7rem;">Cá nhân</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function confirmReset(id, username) {
        Swal.fire({
            title: 'Reset mật khẩu?',
            text: "Mật khẩu của " + username + " sẽ được đặt về: User@123",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Đồng ý reset',
            cancelButtonText: 'Hủy',
            borderRadius: '15px'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '/webbanhang/AuthController/resetUserPassword/' + id;
            }
        });
    }
</script>

<?php include 'app/views/shares/footer.php'; ?>
