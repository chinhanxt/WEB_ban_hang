<?php include 'app/views/shares/header.php'; ?>

<h2 class="mb-4">Giỏ hàng của bạn</h2>

<form id="cart-form" action="/webbanhang/ProductController/checkout" method="POST">
    <div class="card border-0 shadow-sm" style="border-radius: 20px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="border-0 px-4 py-3" style="width: 50px;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="select-all">
                                    <label class="custom-control-label" for="select-all"></label>
                                </div>
                            </th>
                            <th class="border-0 py-3">Sản phẩm</th>
                            <th class="border-0 py-3 text-center">Giá</th>
                            <th class="border-0 py-3 text-center" style="width: 150px;">Số lượng</th>
                            <th class="border-0 py-3 text-right">Tổng cộng</th>
                            <th class="border-0 px-4 py-3 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        foreach ($products as $p):
                            $sum = $p->Price * $p->qty;
                            // Mặc định không cộng vào total ban đầu để user tự tích chọn
                            ?>
                            <tr class="cart-item-row" data-price="<?php echo $p->Price; ?>" data-id="<?php echo $p->Id; ?>">
                                <td class="px-4 py-4">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="selected_ids[]" value="<?php echo $p->Id; ?>" class="custom-control-input item-checkbox" id="check-<?php echo $p->Id; ?>">
                                        <label class="custom-control-label" for="check-<?php echo $p->Id; ?>"></label>
                                    </div>
                                </td>
                                <td class="py-4">
                                    <div class="d-flex align-items-center">
                                        <?php if (isset($p->Image)): ?>
                                            <img src="/webbanhang/<?php echo $p->Image; ?>" class="rounded mr-3 border" style="width: 60px; height: 60px; object-fit: cover;">
                                        <?php endif; ?>
                                        <h6 class="m-0 font-weight-bold"><?php echo htmlspecialchars($p->Name); ?></h6>
                                    </div>
                                </td>
                                <td class="py-4 text-center text-muted"><?php echo number_format($p->Price); ?> VND</td>
                                <td class="py-4 text-center">
                                    <div class="input-group input-group-sm mx-auto" style="width: 110px;">
                                        <div class="input-group-prepend">
                                            <button class="btn btn-outline-secondary btn-qty-minus" type="button" onclick="changeQty(<?php echo $p->Id; ?>, -1)">-</button>
                                        </div>
                                        <input type="number" value="<?php echo $p->qty; ?>" min="1" class="form-control text-center border-0 bg-light font-weight-bold input-qty"
                                            data-id="<?php echo $p->Id; ?>" onchange="updateCart(<?php echo $p->Id; ?>, this.value)">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary btn-qty-plus" type="button" onclick="changeQty(<?php echo $p->Id; ?>, 1)">+</button>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 text-right">
                                    <strong class="text-primary row-total"><?php echo number_format($sum); ?> VND</strong>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <a href="#" class="btn btn-outline-danger btn-sm border-0" 
                                       onclick="return confirmDelete('/webbanhang/ProductController/removeFromCart/<?php echo $p->Id; ?>', 'Sản phẩm này sẽ được xóa khỏi giỏ hàng!')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-5">
        <a href="/webbanhang/ProductController" class="btn btn-link text-muted p-0">
            <i class="fa-solid fa-arrow-left mr-2"></i>Tiếp tục mua sắm
        </a>

        <div class="text-right">
            <div class="bg-white p-4 shadow-sm inline-block rounded" style="display: inline-block; min-width: 350px; border-radius: 20px;">
                <div class="d-flex justify-content-between mb-3 align-items-center">
                    <span class="text-muted">Đã chọn (<span id="selected-count">0</span> sản phẩm):</span>
                    <h4 class="text-primary font-weight-bold mb-0" id="total-price">0 VND</h4>
                </div>
                
                <?php if (!empty($products)): ?>
                    <button type="button" id="btn-checkout" class="btn btn-primary btn-lg btn-block shadow py-3" style="border-radius: 12px;" disabled>
                        Mua hàng <i class="fa-solid fa-arrow-right ml-2"></i>
                    </button>
                <?php else: ?>
                    <div class="alert alert-warning mb-0 border-0 text-center" style="border-radius: 12px;">Giỏ hàng của bạn đang trống</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</form>

<script>
    function calculateTotal() {
        let total = 0;
        let count = 0;
        document.querySelectorAll('.item-checkbox:checked').forEach(checkbox => {
            const row = checkbox.closest('.cart-item-row');
            const price = parseFloat(row.dataset.price);
            const qty = parseInt(row.querySelector('.input-qty').value);
            total += price * qty;
            count++;
        });
        document.getElementById('total-price').innerText = total.toLocaleString() + ' VND';
        document.getElementById('selected-count').innerText = count;
        document.getElementById('btn-checkout').disabled = (count === 0);
    }

    document.getElementById('select-all').addEventListener('change', function() {
        document.querySelectorAll('.item-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        calculateTotal();
    });

    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', calculateTotal);
    });

    function changeQty(id, delta) {
        const input = document.querySelector(`.input-qty[data-id="${id}"]`);
        let newQty = parseInt(input.value) + delta;
        if (newQty < 1) newQty = 1;
        input.value = newQty;
        updateCart(id, newQty);
    }

    function updateCart(id, qty) {
        const input = document.querySelector(`.input-qty[data-id="${id}"]`);
        qty = parseInt(qty);
        
        if (isNaN(qty) || qty < 1) {
            qty = 1;
            input.value = 1;
        }
        
        let params = new URLSearchParams();
        params.append('id', id);
        params.append('qty', qty);

        fetch("/webbanhang/ProductController/updateCartAjax", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: params.toString()
        })
        .then(res => res.json())
        .then(data => {
            console.log("Updated:", data);
            const row = document.querySelector(`.cart-item-row[data-id="${id}"]`);
            if (row) {
                const price = parseFloat(row.dataset.price);
                row.querySelector('.row-total').innerText = (price * qty).toLocaleString() + ' VND';
            }
            calculateTotal();
            document.getElementById("cart-count").innerText = data.count;
        })
        .catch(err => console.error("Error:", err));
    }

    document.getElementById('btn-checkout').addEventListener('click', function() {
        document.getElementById('cart-form').submit();
    });
</script>
<?php include 'app/views/shares/footer.php'; ?>
