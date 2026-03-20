<?php include 'app/views/shares/header.php'; ?>

<h2 class="mb-4 font-weight-bold" style="font-size: 1.5rem;"><i class="fa-solid fa-cart-shopping mr-2 text-primary"></i>GIỎ HÀNG CỦA BẠN</h2>

<form id="cart-form" action="/webbanhang/ProductController/checkout" method="POST">
    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
        <div class="card-body p-0">
            <?php if (!empty($products)): ?>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="bg-light text-muted small text-uppercase" style="font-size: 0.75rem;">
                        <tr>
                            <th class="border-0 px-3 py-2" style="width: 40px;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="select-all">
                                    <label class="custom-control-label" for="select-all"></label>
                                </div>
                            </th>
                            <th class="border-0 py-2">Sản phẩm</th>
                            <th class="border-0 py-2 text-center" style="width: 100px;">Giá</th>
                            <th class="border-0 py-2 text-center" style="width: 120px;">Số lượng</th>
                            <th class="border-0 py-2 text-right">Tổng cộng</th>
                            <th class="border-0 px-3 py-2 text-center"></th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.9rem;">
                        <?php
                        $total = 0;
                        foreach ($products as $p):
                            $sum = $p->Price * $p->qty;
                            ?>
                            <tr class="cart-item-row" data-price="<?php echo $p->Price; ?>" data-id="<?php echo $p->Id; ?>">
                                <td class="px-3 py-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="selected_ids[]" value="<?php echo $p->Id; ?>" class="custom-control-input item-checkbox" id="check-<?php echo $p->Id; ?>">
                                        <label class="custom-control-label" for="check-<?php echo $p->Id; ?>"></label>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex align-items-center">
                                        <?php if (isset($p->Image)): ?>
                                            <img src="/webbanhang/<?php echo $p->Image; ?>" class="rounded mr-2 border" style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php endif; ?>
                                        <h6 class="m-0 font-weight-bold" style="font-size: 0.95rem;"><?php echo htmlspecialchars($p->Name); ?></h6>
                                    </div>
                                </td>
                                <td class="py-3 text-center text-muted small"><?php echo number_format($p->Price); ?> <small>VND</small></td>
                                <td class="py-3 text-center">
                                    <div class="input-group input-group-sm mx-auto" style="width: 90px;">
                                        <div class="input-group-prepend">
                                            <button class="btn btn-outline-secondary btn-qty-minus py-0 px-2" type="button" onclick="changeQty(<?php echo $p->Id; ?>, -1)">-</button>
                                        </div>
                                        <input type="number" value="<?php echo $p->qty; ?>" min="1" class="form-control text-center border-0 bg-light font-weight-bold input-qty"
                                            data-id="<?php echo $p->Id; ?>" onchange="updateCart(<?php echo $p->Id; ?>, this.value)" style="height: 30px; font-size: 0.9rem;">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary btn-qty-plus py-0 px-2" type="button" onclick="changeQty(<?php echo $p->Id; ?>, 1)">+</button>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-right">
                                    <strong class="text-primary row-total" style="font-size: 0.95rem;"><?php echo number_format($sum); ?> VND</strong>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <a href="#" class="btn btn-light btn-sm rounded-circle" style="width: 30px; height: 30px; padding: 0; line-height: 30px;"
                                       onclick="return confirmDelete('/webbanhang/ProductController/removeFromCart/<?php echo $p->Id; ?>', 'Sản phẩm này sẽ được xóa khỏi giỏ hàng!')">
                                        <i class="fa fa-trash text-danger small"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="alert alert-info text-center m-4 border-0 shadow-sm" style="border-radius: 12px;">
                    <i class="fa-solid fa-box-open fa-lg mr-2"></i>Giỏ hàng của bạn đang trống.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4">
        <a href="/webbanhang/ProductController" class="btn btn-link text-muted p-0" style="font-size: 0.9rem;">
            <i class="fa-solid fa-arrow-left mr-2"></i>Tiếp tục mua sắm
        </a>

        <div class="text-right">
            <div class="bg-white p-3 shadow-sm inline-block rounded" style="display: inline-block; min-width: 320px; border-radius: 15px;">
                <div class="d-flex justify-content-between mb-2 align-items-center">
                    <span class="text-muted small">Đã chọn (<span id="selected-count">0</span> sản phẩm):</span>
                    <h5 class="text-primary font-weight-bold mb-0" id="total-price" style="font-size: 1.1rem;">0 VND</h5>
                </div>
                
                <?php if (!empty($products)): ?>
                    <button type="button" id="btn-checkout" class="btn btn-primary btn-sm btn-block shadow py-2" style="border-radius: 10px; font-size: 0.95rem;" disabled>
                        MUA HÀNG <i class="fa-solid fa-arrow-right ml-2"></i>
                    </button>
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

    // Initial calculation on page load
    document.addEventListener('DOMContentLoaded', calculateTotal);
</script>
<?php include 'app/views/shares/footer.php'; ?>
