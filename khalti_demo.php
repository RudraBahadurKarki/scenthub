<?php
require_once 'includes/functions.php';

$token = $_GET['token'] ?? '';

if (
    empty($token) ||
    empty($_SESSION['demo_payment_token']) ||
    !hash_equals($_SESSION['demo_payment_token'], $token)
) {
    http_response_code(403);
    exit('Invalid or expired payment session.');
}

$amount = number_format(
    (float) $_SESSION['demo_payment_amount'],
    2
);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $_SESSION['demo_payment_completed'] = true;
    $_SESSION['demo_payment_method'] = 'Khalti';

    header('Content-Type: application/json');

    echo json_encode([
        'success' => true
    ]);

    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Khalti Demo Payment</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background: #f6f1ff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .payment-card {
            width: 430px;
            max-width: 100%;
            border: none;
            border-radius: 22px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 25px 60px rgba(0, 0, 0, .18);
        }


        .payment-header {
            background: linear-gradient(
                135deg,
                #5C2D91,
                #7b4dff
            );

            color: #ffffff;
            padding: 30px;
            text-align: center;
        }

        .logo {
            width: 150px;
            max-width: 100%;
            display: block;
            margin: auto;
        }

        .payment-header h3 {
            margin-top: 15px;
            margin-bottom: 0;
            font-weight: 700;
        }


        .payment-content {
            padding: 28px;
        }


        .demo {
            background: #fff9e6;
            border-left: 5px solid #d4af37;
            padding: 13px 15px;
            border-radius: 10px;
            color: #555;
            margin-bottom: 22px;
        }

        .demo strong {
            color: #8a6a00;
        }


        .amount {
            background: #faf7ff;
            border: 2px dashed #6f42c1;
            border-radius: 15px;
            padding: 18px;
            margin-bottom: 25px;
            text-align: center;
        }

        .amount small {
            color: #777;
            display: block;
            margin-bottom: 4px;
        }

        .amount h2 {
            color: #6f42c1;
            font-weight: 700;
            margin: 0;
        }


        #paymentForm {
            margin-top: 5px;
        }

        .payment-input {
            width: 100%;
            height: 52px;
            border: 1px solid #d8d8d8;
            border-radius: 12px;
            padding: 0 15px;
            margin-bottom: 15px;
            outline: none;
            transition: .2s ease;
            background: #ffffff;
        }



        .payment-input:focus {
            border-color: #d4af37;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, .14);
        }

        .payment-input::placeholder {
            color: #999;
        }


        .btn-khalti {
            width: 100%;
            height: 54px;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
            background: #5C2D91;
            transition: .25s ease;
            cursor: pointer;
        }

        .btn-khalti:hover:not(:disabled) {
            background: #4b217c;
            transform: translateY(-1px);
        }

        .btn-khalti:disabled {
            opacity: .75;
            cursor: wait;
        }


        .success-box {
            display: none;
            text-align: center;
            padding: 25px 10px;
        }

        .success-icon {
            width: 85px;
            height: 85px;
            margin: 0 auto 18px;
            border-radius: 50%;
            background: #d4af37;
            color: #ffffff;
            font-size: 48px;
            line-height: 85px;
            font-weight: 700;
        }

        .success-box h2 {
            color: #6f42c1;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .success-box p {
            color: #777;
            margin-bottom: 0;
        }


        .footer {
            text-align: center;
            margin-top: 20px;
            color: #888;
            font-size: 13px;
        }

        @media (max-width: 480px) {

            body {
                padding: 12px;
            }

            .payment-header {
                padding: 25px 20px;
            }

            .payment-content {
                padding: 22px;
            }

        }

    </style>

</head>

<body>

    <div class="payment-card">


        <div id="paymentSection">

            <div class="payment-header">

                <img
                    src="assets/images/khalti.png"
                    class="logo"
                    alt="Khalti"
                >

                <h3>
                    Demo Khalti Payment
                </h3>

            </div>

            <div class="payment-content">

                <div class="demo">

                    <strong>Academic Project Demo</strong><br>

                    No real payment is processed.

                </div>

                <div class="amount">

                    <small>Total Amount</small>

                    <h2>
                        Rs. <?php echo $amount; ?>
                    </h2>

                </div>

                <form
                    method="post"
                    id="paymentForm"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php echo clean(csrf_token()); ?>"
                    >

                    <input
                        type="text"
                        name="phone"
                        class="payment-input"
                        placeholder="9800000000"
                        inputmode="numeric"
                        maxlength="10"
                        required
                    >

                    <input
                        type="password"
                        name="pin"
                        class="payment-input"
                        placeholder="Khalti PIN"
                        maxlength="6"
                        required
                    >

                    <button
                        type="submit"
                        id="payBtn"
                        class="btn-khalti"
                    >
                        Pay Rs. <?php echo $amount; ?>
                    </button>

                </form>

                <div class="footer">
                    Powered by Khalti Demo Gateway
                </div>

            </div>

        </div>



        <div
            id="successBox"
            class="success-box"
        >

            <div class="success-icon">
                ✓
            </div>

            <h2>
                Payment Received
            </h2>

            <p>
                Redirecting back to ScentHub...
            </p>

        </div>

    </div>


    <script>

        document
            .getElementById("paymentForm")
            .addEventListener("submit", async function (e) {

                e.preventDefault();

                const form = this;
                const btn = document.getElementById("payBtn");



                btn.disabled = true;

                btn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>' +
                    'Processing Payment...';

                try {

                    const formData = new FormData(form);

                    const response = await fetch(
                        window.location.href,
                        {
                            method: "POST",
                            body: formData
                        }
                    );

                    if (!response.ok) {
                        throw new Error("Payment request failed.");
                    }

                    const result = await response.json();

                    if (!result.success) {
                        throw new Error("Payment failed.");
                    }



                    document.getElementById(
                        "paymentSection"
                    ).style.display = "none";



                    document.getElementById(
                        "successBox"
                    ).style.display = "block";



                    setTimeout(function () {

                        if (
                            window.opener &&
                            !window.opener.closed
                        ) {

                            window.opener.paymentCompleted();

                        }

                        window.close();

                    }, 1800);

                } catch (error) {



                    btn.disabled = false;

                    btn.innerHTML =
                        "Pay Rs. <?php echo $amount; ?>";

                    alert(
                        "Payment could not be completed. Please try again."
                    );

                }

            });

    </script>

</body>

</html>