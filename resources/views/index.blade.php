<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        h1 {
            text-align: center;
        }

        h3 {
            height: 25px;
            padding: 10px;
            font-weight: bold;
            text-align: center;
            background: rgb(82, 155, 75);
            color: whitesmoke;
        }

        label {
            margin: auto;
        }

        .form {
            margin: auto;
            width: 500px;
            height: 230px;
            background: rgb(204, 203, 203)
        }

        input {
            height: 30px;
            border: 1px solid;
            border-radius: 2px;
            margin-bottom: 3px;
        }

        .amount {
            padding: 10px;
        }

        button {
            margin-top: 10px;
            margin-left: 30%;
            font-size: 18px;
            height: 30px;
            border: none;
            border-radius: 3px;
            color: whitesmoke;
            text-align: center;
            cursor: pointer;
            width: 80px;
            background: rgb(34, 70, 255);

        }

        .payment {
            display: inline-flex;
            margin-top: 3px;
        }
    </style>
</head>

<body>
    <h1>Welcome to the Payment Integration</h1>
    <div class="form">
        <h3 for="">Payment System</h3>
        <div class="amount">
            <label for="amount"><b>Amount : </b></label>
            <input type="text" name="amount" id="amount"><br>
            <div class="payment">
                <label for="amount"><b>Select Payment Method : </b></label><br>
                <input type="radio" name="payment_method" value="esewa">
                <label for="">Esewa Pay</label><br>
                <input type="radio" name="payment_method" value="khalti">
                <label for="">Khalti Pay</label><br>
            </div>
            <button id="saveBtn">Submit</button>
        </div>
    </div>
    <h4 class="response"></h4>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/crypto-js.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/hmac-sha256.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/enc-base64.min.js"></script>
    <script>
        $(document).ready(function() {
            var successUrl = "{{ route('paymentSuccess') }}";
            var failureUrl = "{{ route('paymentFailure') }}";
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#saveBtn').click(function(event) {
                event.preventDefault(); 
                const paymentMethod = $('input[name="payment_method"]:checked').val();
                var url = "{{ route('paymentMethod', ':payment_method') }}";
                url = url.replace(':payment_method', paymentMethod);
                const amount = $('#amount').val();
                const transaction_uuid = crypto.randomUUID();
                if (paymentMethod == 'esewa') {
                    const obj = {
                        "amount": amount,
                        "tax_amount": 0,
                        "total_amount": amount,
                        "transaction_uuid": transaction_uuid,
                        "product_code": "EPAYTEST",
                        "product_service_charge": 0,
                        "product_delivery_charge": 0,
                        "success_url": successUrl,
                        "failure_url": failureUrl,
                        "signed_field_names": "total_amount,transaction_uuid,product_code",
                        "_token": $('meta[name="csrf-token"]').attr('content'),
                    };

                    var hashString =`total_amount=${amount},transaction_uuid=${transaction_uuid},product_code=EPAYTEST`;
                    var hash = CryptoJS.HmacSHA256(hashString, "8gBm/:&EnhH.1/q");
                    var hashInBase64 = CryptoJS.enc.Base64.stringify(hash);
                    obj.signature = hashInBase64;
                    ajaxSubmit(url, obj);
                    
                } else if (paymentMethod == 'khalti') {
                    data = {
                        "return_url": failureUrl,
                        "website_url": successUrl,
                        "amount": amount * 100,
                        "purchase_order_id": transaction_uuid,
                        "purchase_order_name": "Test",
                        "customer_info": {
                            "name": "Test Bahadur",
                            "email": "test@khalti.com",
                            "phone": "9800000001"
                        }
                    }
                    ajaxSubmit(url, data);
                }
            });

            function ajaxSubmit(url, data) {
                $.ajax({
                    url: url,
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    success: function(response) {
                        if(response.method == 'esewa'){
                            console.log(response.data);
                            $('.response').text(response.data);
                        }
                        if(response.method == 'khalti'){
                            window.location.href = response.redirect_url;
                        }
                        console.log('Success:', response);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        if (jqXHR.status === 409) {
                            console.error('Conflict:', errorThrown);
                        } else {
                            console.error('Error:', textStatus, errorThrown);
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
