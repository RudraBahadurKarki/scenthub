<?php
require_once 'includes/auth.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

$page_title = 'Cart';

include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();


    if (
        (isset($_POST['update']) || isset($_POST['auto_update']))
        && isset($_POST['qty'])
        && is_array($_POST['qty'])
    ) {

        foreach ($_POST['qty'] as $cartId => $qty) {

            $cartId = (int) $cartId;
            $qty = max(1, (int) $qty);

            $stmt = mysqli_prepare(
                $conn,
                "SELECT p.stock
                 FROM cart c
                 JOIN products p ON c.product_id = p.id
                 WHERE c.id = ?
                 AND c.user_id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                'ii',
                $cartId,
                $_SESSION['user_id']
            );

            mysqli_stmt_execute($stmt);

            $row = mysqli_fetch_assoc(
                mysqli_stmt_get_result($stmt)
            );

            mysqli_stmt_close($stmt);

            if (!$row) {
                continue;
            }

            if ((int) $row['stock'] <= 0) {

                flash(
                    'danger',
                    'One of the selected products is out of stock.'
                );

                continue;
            }

            if ($qty > (int) $row['stock']) {

                $qty = (int) $row['stock'];

                flash(
                    'danger',
                    "Only {$row['stock']} item(s) left in stock."
                );
            }

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE cart
                 SET quantity = ?
                 WHERE id = ?
                 AND user_id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                'iii',
                $qty,
                $cartId,
                $_SESSION['user_id']
            );

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }


    if (isset($_POST['checkout_selected'])) {

        $selectedItems = $_POST['selected_items'] ?? [];

        $selectedItems = array_values(
            array_unique(
                array_filter(
                    array_map('intval', $selectedItems),
                    fn($id) => $id > 0
                )
            )
        );

        if (!$selectedItems) {

            flash(
                'warning',
                'Please select at least one product to checkout.'
            );

            redirect('cart.php');
        }

        $_SESSION['checkout_selected_items'] = $selectedItems;

        redirect('checkout.php');
    }

    if (isset($_POST['remove'])) {

        $cartId = (int) $_POST['remove'];

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM cart
             WHERE id = ?
             AND user_id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'ii',
            $cartId,
            $_SESSION['user_id']
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        flash('success', 'Item removed.');
    }

    redirect('cart.php');
}


$stmt = mysqli_prepare(
    $conn,
    "SELECT
        cart.id AS cart_id,
        cart.quantity,
        p.*,
        b.name AS brand
     FROM cart
     JOIN products p ON cart.product_id = p.id
     JOIN brands b ON p.brand_id = b.id
     WHERE cart.user_id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    'i',
    $_SESSION['user_id']
);

mysqli_stmt_execute($stmt);

$items = mysqli_fetch_all(
    mysqli_stmt_get_result($stmt),
    MYSQLI_ASSOC
);

mysqli_stmt_close($stmt);

$total = 0;
?>

<section class="page-hero">

    <div class="container">

        <span class="eyebrow">Selected Edit</span>

        <h1>Your Cart</h1>

        <p>
            Review your selected signature scents.
        </p>

    </div>

</section>

<section class="section-pad">

    <div class="container">

        <?php if (!$items): ?>

            <div class="empty-state text-center">

                <h3>Your cart is empty.</h3>

                <p class="mb-4" style="color:#d4af37;">
                    Discover premium fragrances and add your favourites.
                </p>

                <a href="shop.php" class="btn btn-gold">
                    Continue Shopping
                </a>

            </div>

        <?php else: ?>

            <form method="post" id="cartForm">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo clean(csrf_token()); ?>"
                >

                <input
                    type="hidden"
                    name="auto_update"
                    value="1"
                >

                <div class="lux-panel cart-panel reveal-on-scroll">

                    <div class="table-responsive">

                        <table class="table luxury-table align-middle">

                            <thead>

                                <tr class="text-center">

                                    <th>
                                        Select
                                    </th>

                                    <th class="text-start">
                                        Product
                                    </th>

                                    <th>
                                        Price
                                    </th>

                                    <th>
                                        Quantity
                                    </th>

                                    <th>
                                        Subtotal
                                    </th>

                                    <th>
                                        Action
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($items as $item): ?>

                                    <?php
                                    $sub = $item['price'] * $item['quantity'];
                                    $total += $sub;
                                    ?>

                                    <tr class="align-middle text-center">

                                        <td>

                                            <input
                                                type="checkbox"
                                                class="form-check-input cart-select"
                                                name="selected_items[]"
                                                value="<?php echo $item['cart_id']; ?>"
                                                checked
                                            >

                                        </td>

                                        <td class="text-start">

                                            <div class="d-flex align-items-center gap-3">

                                                <img
                                                    src="<?php echo product_image($item['image']); ?>"
                                                    alt="<?php echo clean($item['name']); ?>"
                                                    class="cart-img"
                                                >

                                                <div>

                                                    <h6 class="mb-1">
                                                        <?php echo clean($item['name']); ?>
                                                    </h6>

                                                    <small>
                                                        <?php echo clean($item['brand']); ?>
                                                    </small>

                                                </div>

                                            </div>

                                        </td>

                                        <td
                                            class="text-center"
                                            data-price="<?php echo (float) $item['price']; ?>"
                                        >
                                            <?php echo money($item['price']); ?>
                                        </td>
                                        <td>
                                            <input
                                                type="number"
                                                class="form-control qty-input text-center"
                                                data-cart-id="<?php echo (int) $item['cart_id']; ?>"
                                                data-stock="<?php echo (int) $item['stock']; ?>"
                                                value="<?php echo min((int) $item['quantity'], (int) $item['stock']); ?>"
                                                min="1"
                                                max="<?php echo (int) $item['stock']; ?>"
                                                step="1"
                                            >

                                            <small
                                                class="stock-message text-danger fw-semibold"
                                                style="display:none;"
                                            ></small>

                                            <?php if ((int) $item['stock'] <= 5): ?>
                                                <small class="low-stock-message text-danger fw-semibold">
                                                    Only <?php echo (int) $item['stock']; ?> left in stock.
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold">

                                            <?php echo money($sub); ?>

                                        </td>

                                        <td>

                                            <button
                                                type="submit"
                                                class="btn btn-outline-danger btn-sm"
                                                name="remove"
                                                value="<?php echo $item['cart_id']; ?>"
                                                onclick="return confirm('Remove this item from your cart?');"
                                            >
                                                Remove
                                            </button>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

                <?php
                $outOfStock = false;

                foreach ($items as $item) {

                    if ($item['quantity'] > $item['stock']) {
                        $outOfStock = true;
                        break;
                    }
                }
                ?>

                <div class="cart-summary lux-panel mt-4 d-flex justify-content-between align-items-center flex-wrap gap-3">

                    <div>

                        <span class="eyebrow">
                            Cart Total
                        </span>

                        <h3 class="mb-0" id="cartTotal">
                            <?php echo money($total); ?>
                        </h3>

                    </div>

                    <?php if ($outOfStock): ?>

                        <button
                            type="button"
                            class="btn btn-secondary"
                            disabled
                        >
                            Insufficient Stock
                        </button>

                    <?php else: ?>

                        <button
                            type="submit"
                            name="checkout_selected"
                            value="1"
                            class="btn btn-gold"
                        >
                            Checkout
                        </button>

                    <?php endif; ?>

                </div>

            </form>

        <?php endif; ?>

    </div>

</section>

<script>



document.querySelectorAll(".qty-input").forEach(input => {

    let saveTimer = null;


    function validateQuantity() {

        const stock =
            parseInt(input.dataset.stock, 10) || 0;

        let quantity =
            parseInt(input.value, 10);

        const message =
            input.parentElement.querySelector(".stock-message");




        if (stock <= 0) {

            input.value = 1;

            if (message) {
                message.textContent = "Out of stock.";
                message.style.display = "block";
            }

            updateSelectedTotal();

            return false;
        }




        if (isNaN(quantity) || quantity < 1) {

            input.value = 1;

            if (message) {
                message.textContent = "";
                message.style.display = "none";
            }

            updateSelectedTotal();

            return true;
        }




        if (quantity >= stock) {



            if (quantity > stock) {
                input.value = stock;
            }


            if (message) {

                message.textContent =
                    "Only " +
                    stock +
                    " item(s) left in stock.";

                message.style.display = "block";
            }

        } else {


            if (message) {
                message.textContent = "";
                message.style.display = "none";
            }

        }


        updateSelectedTotal();

        return true;
    }


    function scheduleSave() {


        clearTimeout(saveTimer);


        saveTimer = setTimeout(() => {

            saveQuantity();

        }, 1000);
    }



    function saveQuantity() {

        const stock =
            parseInt(input.dataset.stock, 10) || 0;

        let quantity =
            parseInt(input.value, 10) || 1;



        if (quantity < 1) {
            quantity = 1;
            input.value = 1;
        }

        if (quantity > stock) {
            quantity = stock;
            input.value = stock;
        }




        const selectedItems = [];

        document
            .querySelectorAll(".cart-select:checked")
            .forEach(checkbox => {

                selectedItems.push(checkbox.value);

            });


        sessionStorage.setItem(
            "selectedCartItems",
            JSON.stringify(selectedItems)
        );




        fetch("update_cart.php", {

            method: "POST",

            headers: {
                "Content-Type":
                    "application/x-www-form-urlencoded"
            },

            body:
                "cart_id=" +
                encodeURIComponent(
                    input.dataset.cartId
                ) +

                "&quantity=" +
                encodeURIComponent(quantity) +

                "&csrf_token=" +
                encodeURIComponent(
                    "<?php echo csrf_token(); ?>"
                )

        })
        .then(response => {

            if (!response.ok) {
                throw new Error(
                    "Cart update failed."
                );
            }

            return response.text();

        })
        .then(() => {


            location.reload();

        })
        .catch(error => {

            console.error(
                "Cart update failed:",
                error
            );

        });

    }




    input.addEventListener("input", function () {



        validateQuantity();



        scheduleSave();

    });



    input.addEventListener("change", function () {



        validateQuantity();



        scheduleSave();

    });

});



function updateSelectedTotal() {

    let total = 0;


    document
        .querySelectorAll(".cart-select:checked")
        .forEach(checkbox => {

            const row =
                checkbox.closest("tr");


            if (!row) {
                return;
            }


            const priceCell =
                row.querySelector(
                    "td[data-price]"
                );


            const quantityInput =
                row.querySelector(
                    ".qty-input"
                );


            if (!priceCell || !quantityInput) {
                return;
            }


            const price =
                parseFloat(
                    priceCell.dataset.price
                ) || 0;


            const quantity =
                parseInt(
                    quantityInput.value,
                    10
                ) || 0;


            total += price * quantity;

        });


    const totalElement =
        document.getElementById("cartTotal");


    if (!totalElement) {
        return;
    }


    totalElement.textContent =
        "Rs. " +
        total.toLocaleString("en-IN", {

            minimumFractionDigits: 2,

            maximumFractionDigits: 2

        });

}



document
    .querySelectorAll(".cart-select")
    .forEach(checkbox => {

        checkbox.addEventListener(
            "change",
            updateSelectedTotal
        );

    });


updateSelectedTotal();


const savedSelections =
    sessionStorage.getItem(
        "selectedCartItems"
    );


if (savedSelections) {

    try {

        const selectedItems =
            JSON.parse(
                savedSelections
            );


        document
            .querySelectorAll(".cart-select")
            .forEach(checkbox => {

                checkbox.checked =
                    selectedItems.includes(
                        checkbox.value
                    );

            });


        updateSelectedTotal();

    } catch (error) {

        console.error(
            "Could not restore cart selection.",
            error
        );

    }


    sessionStorage.removeItem(
        "selectedCartItems"
    );

}

</script>
<?php include 'includes/footer.php'; ?>
<script>
window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});
</script>

