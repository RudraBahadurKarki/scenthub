<?php
session_start();

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
    $_SESSION['demo_payment_completed'] = true;
    $_SESSION['demo_payment_method'] = 'eSewa';

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>eSewa Demo Payment</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: Segoe UI, Arial, sans-serif;
            background: #eef7ef;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
            width: 430px;
            border: none;
            border-radius: 22px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 25px 60px rgba(0, 0, 0, .18);
        }

        .header {
            background: linear-gradient(135deg, #4CAF50, #0AA94F);
            color: #fff;
            text-align: center;
            padding: 30px;
        }

        .logo {
            width: 150px;
            display: block;
            margin: auto;
        }

        .amount {
            background: #f4fff6;
            border: 2px dashed #4CAF50;
            border-radius: 15px;
            padding: 18px;
            text-align: center;
            margin: 25px 0;
        }

        .amount small {
            color: #666;
        }

        .amount h2 {
            margin: 0;
            color: #15803d;
            font-weight: 700;
        }

        .form-control {
            height: 52px;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .btn-esewa {
            width: 100%;
            height: 54px;
            border: none;
            border-radius: 12px;
            background: #60BB46;
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            transition: .25s;
        }

        .btn-esewa:hover {
            background: #4CA63A;
        }

        .demo {
            background: #fff8dc;
            border-left: 5px solid orange;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            color: #555;
        }

        .footer {
            text-align: center;
            color: #888;
            margin-top: 20px;
            font-size: 13px;
        }

        #successBox {
            display: none;
            text-align: center;
            padding: 35px 10px;
        }

        .tick {
            width: 90px;
            height: 90px;
            margin: auto;
            border-radius: 50%;
            background: #28a745;
            color: #fff;
            font-size: 52px;
            line-height: 90px;
        }
    </style>

</head>

<body>

    <div class="card">

        <div class="header">

            <img src="assets/images/esewa.png" class="logo">

            <h3 class="mt-3 mb-0">
                Demo eSewa Payment
            </h3>

        </div>

        <div class="p-4">

            <div id="paymentSection">

                <div class="demo">

                    <strong>Academic Project Demo</strong><br>

                    No real payment is processed.

                </div>

                <div class="amount">

                    <small>Total Amount</small>

                    <h2>Rs. <?php echo $amount; ?></h2>

                </div>

                <form id="paymentForm">

                    <input type="text" class="form-control" placeholder="9800000000" required>

                    <input type="password" class="form-control" placeholder="MPIN" required>

                    <button type="submit" id="payBtn" class="btn-esewa">

                        Pay Rs. <?php echo $amount; ?>

                    </button>

                </form>

                <div class="footer">

                    Powered by eSewa Demo Gateway

                </div>

            </div>

            <div id="successBox">

                <div class="tick">✓</div>

                <h2 class="text-success mt-4 fw-bold">

                    Payment Received

                </h2>

                <p class="text-muted">

                    Redirecting back to ScentHub...

                </p>

            </div>

        </div>

    </div>

    <script>

       document.getElementById("paymentForm").addEventListener("submit", async function (e) {
    e.preventDefault();

    const btn = document.getElementById("payBtn");

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing Payment...';

    try {
        const response = await fetch(window.location.href, {
            method: "POST"
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error("Payment failed.");
        }

        document.getElementById("paymentSection").style.display = "none";
        document.getElementById("successBox").style.display = "block";

        setTimeout(function () {
            if (window.opener && !window.opener.closed) {
                window.opener.paymentCompleted();
            }

            window.close();
        }, 1800);

    } catch (error) {
        btn.disabled = false;
        btn.innerHTML = "Pay Rs. <?php echo $amount; ?>";
        alert("Payment could not be completed. Please try again.");
    }
});

    </script>

</body>

</html>