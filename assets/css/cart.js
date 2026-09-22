

$(function () {
    $(document).on('click', '.add-to-cart-btn', function () {
        addToCart($(this));
    });

    $(document).on('click', '.buy-now-btn', function () {
        addToCart($(this), function (res) {
            window.location.href = 'checkout.php?cart_items_id=' + encodeURIComponent(res.cart_items_id);
        });
    });

    function addToCart($btn, onSuccess) {
        const productId = $btn.data('id');
        const itemType  = $btn.data('type') || 'product';
        const $qtyInput = $('#productQuantity, input[name="quantity"]').first();
        const quantity  = $qtyInput.length ? (parseInt($qtyInput.val(), 10) || 1) : 1;

        if (!productId) {
            console.error('Button is missing data-id:', $btn[0]);
            return;
        }

        const originalText = $btn.html();
        $btn.prop('disabled', true).text('Adding...');

        $.post('ajax/add_to_cart.php', {
            product_id: productId,
            item_type: itemType,
            quantity: quantity
        }, null, 'json')
        .done(function (res) {
            console.log('add_to_cart.php response:', res);
            if (res.success) {
                updateCartBadge(res.cart_count);
                $btn.text('Added ✓');
                setTimeout(() => $btn.html(originalText), 1200);
                if (typeof onSuccess === 'function') onSuccess(res);
            } else if (res.reason === 'not_logged_in') {
                window.location.href = res.redirect || 'login.php';
            } else {
                alert(res.message ? ('Could not add that item: ' + res.message) : 'Could not add that item to your cart. Please try again.');
                $btn.html(originalText);
            }
        })
        .fail(function (jqXHR) {
            console.error('add_to_cart.php request failed:', jqXHR.status, jqXHR.responseText);
            alert('Something went wrong adding that item. Please try again.');
            $btn.html(originalText);
        })
        .always(function () {
            $btn.prop('disabled', false);
        });
    }

    function updateCartBadge(count) {

        $('#cartCount').text(count);
    }


    const $selectAll     = $('#selectAllItems');
    const $deleteBtn     = $('#deleteSelectedBtn');
    const $selectedTotal = $('#selectedTotal');

    if ($selectAll.length === 0) {
        return; 
    }

    function getRowCheckboxes() {
        return $('.cart-item-checkbox');
    }

    function recalcSelection() {
        let total = 0;
        let anyChecked = false;

        getRowCheckboxes().each(function () {
            if (this.checked) {
                anyChecked = true;
                const price = parseFloat($(this).data('price')) || 0;
                const qty   = parseInt($(this).data('quantity'), 10) || 0;
                total += price * qty;
            }
        });

        $selectedTotal.text('Rs. ' + total.toFixed(2));
        $deleteBtn.prop('disabled', !anyChecked);

       
        $selectAll.prop('checked', anyChecked && getRowCheckboxes().length === getRowCheckboxes().filter(':checked').length);
    }

    
    $selectAll.on('change', function () {
        getRowCheckboxes().prop('checked', this.checked);
        recalcSelection();
    });

  
    $(document).on('change', '.cart-item-checkbox', recalcSelection);

  
    $(document).on('click', '.single-delete-btn', function () {
        const $btn = $(this);
        const id = $btn.data('id');

        if (!confirm('Remove this item from your cart?')) return;

        $.post('ajax/remove_from_cart.php', { cart_items_id: [id] }, null, 'json')
            .done(function (res) {
                if (res.success) {
                    $('#cart-row-' + id).remove();
                    updateCartBadge(res.cart_count);
                    recalcSelection();
                    refreshEmptyStateIfNeeded();
                } else {
                    alert('Could not remove that item. Please try again.');
                }
            })
            .fail(function () {
                alert('Something went wrong removing that item.');
            });
    });

    
    $deleteBtn.on('click', function () {
        const ids = getRowCheckboxes().filter(':checked').map(function () {
            return $(this).val();
        }).get();

        if (ids.length === 0) return;
        if (!confirm(`Remove ${ids.length} item(s) from your cart?`)) return;

        $.post('ajax/remove_from_cart.php', { cart_items_id: ids }, null, 'json')
            .done(function (res) {
                if (res.success) {
                    ids.forEach(id => $('#cart-row-' + id).remove());
                    updateCartBadge(res.cart_count);
                    recalcSelection();
                    refreshEmptyStateIfNeeded();
                } else {
                    alert('Could not remove the selected items. Please try again.');
                }
            })
            .fail(function () {
                alert('Something went wrong removing those items.');
            });
    });

    function refreshEmptyStateIfNeeded() {
        if ($('.cart-item-row').length === 0) {
            $('#cartItemsList').html(
                '<div class="empty-cart"><p>Your cart is empty.</p>' +
                '<a href="decor.php" class="continue-shopping-btn">Continue Shopping</a></div>'
            );
            $('.cart-toolbar').remove();
            $('#cartSummary').hide();
        }
    }
    $('#checkoutBtn').on('click', function () {
        let ids = getRowCheckboxes().filter(':checked').map(function () {
            return $(this).val();
        }).get();

        if (ids.length === 0) {
            ids = getRowCheckboxes().map(function () { return $(this).val(); }).get();
        }
        if (ids.length === 0) return;

        const query = ids.map(id => 'cart_items_id[]=' + encodeURIComponent(id)).join('&');
        window.location.href = 'checkout.php?' + query;
    });
    recalcSelection();
});