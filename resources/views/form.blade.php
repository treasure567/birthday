<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Birthday Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
            color: #555;
        }

        input[type="text"], input[type="file"], select {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .input-group {
            display: flex;
            align-items: center;
        }

        .input-group input[type="text"] {
            width: calc(100% - 80px);
            margin-right: 10px;
        }

        .input-group button {
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            padding-top: 10px;
            margin-bottom: 20px;
            width: 70px;
            text-align: center;
        }

        .input-group button:disabled {
            background-color: #007bff;
            cursor: not-allowed;
        }

        .input-group button.loading {
            background-color: #ffc107;
            cursor: wait;
        }

        .date-group {
            display: flex;
            justify-content: space-between;
        }

        .date-group select {
            width: 48%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 18px;
        }

        button[type="submit"]:disabled {
            background-color: #218838;
            cursor: not-allowed;
        }

        button.loading {
            background-color: #ffc107;
            cursor: wait;
        }

        /* Toast Styles */
        #toast {
            visibility: hidden;
            min-width: 250px;
            margin-left: -125px;
            color: #fff;
            text-align: center;
            border-radius: 8px;
            padding: 16px;
            position: fixed;
            z-index: 1;
            left: 50%;
            top: 30px;
            font-size: 17px;
        }

        #toast.show {
            visibility: visible;
            -webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
            animation: fadein 0.5s, fadeout 0.5s 2.5s;
        }

        .toast-success {
            background-color: rgb(84, 226, 84);
        }

        .toast-error {
            background-color: rgb(243, 85, 85);
        }

        .toast-info {
            background-color: rgb(88, 204, 243);
        }

        @-webkit-keyframes fadein {
            from {top: 0; opacity: 0;}
            to {top: 30px; opacity: 1;}
        }

        @keyframes fadein {
            from {top: 0; opacity: 0;}
            to {top: 30px; opacity: 1;}
        }

        @-webkit-keyframes fadeout {
            from {top: 30px; opacity: 1;}
            to {top: 0; opacity: 0;}
        }

        @keyframes fadeout {
            from {top: 30px; opacity: 1;}
            to {top: 0; opacity: 0;}
        }
    </style>
</head>
<body>
    <!-- Toast Notification -->
    <div id="toast"></div>

    <div class="container">
        <h2>Birthday Form</h2>
        <form id="birthdayForm" method="POST" enctype="multipart/form-data">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="picture">Upload Picture:</label>
            <input type="file" id="picture" name="picture" accept="image/*" required>

            <label for="gender">Gender & Status:</label>
            <div class="date-group">
                <select id="gender" name="gender" required>
                    <option value="" disabled selected>Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>

                <select id="status" name="status" required>
                    <option value="" disabled selected>Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <label for="birthDate">Date of Birth:</label>
            <div class="date-group">
                <select id="month" name="month" required>
                    <option value="" disabled selected>Month</option>
                    <option value="1">January</option>
                    <option value="2">February</option>
                    <option value="3">March</option>
                    <option value="4">April</option>
                    <option value="5">May</option>
                    <option value="6">June</option>
                    <option value="7">July</option>
                    <option value="8">August</option>
                    <option value="9">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>

                <select id="day" name="day" required>
                    <option value="" disabled selected>Day</option>
                </select>
            </div>

            <label for="whatsapp">WhatsApp Number: E.g <span style="color: red">2348157002782</span></label>
            <div class="input-group">
                <input type="text" id="whatsapp" name="whatsapp" placeholder="Enter WhatsApp number" required>
                <button type="button" id="sendOtp">OTP</button>
            </div>

            <label for="otp">OTP Code:</label>
            <input type="text" id="otp" name="otp" required>

            <button type="submit" id="submitButton">Submit</button>
            <hr>
            Built by <a href="https://treasureuvietobore.com">Treasure Uvietobore</a>
        </form>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#month').on('change', function() {
            const month = $(this).val();
            const daySelect = $('#day');
            daySelect.empty().append('<option value="" disabled selected>Day</option>');
            const daysInMonth = new Date(2024, month, 0).getDate();
            for (let i = 1; i <= daysInMonth; i++) {
                daySelect.append(`<option value="${i}">${i}</option>`);
            }
        });

        $('#sendOtp').on('click', function() {
            const whatsappNumber = $('#whatsapp').val();
            const $button = $(this);
            var _token = "{{ csrf_token() }}"
            if (whatsappNumber) {
                $button.text('Wait...').prop('disabled', true).addClass('loading');
                $.ajax({
                    url: "{{ route('sendotp') }}",
                    method: 'POST',
                    data: JSON.stringify({ _token: _token, whatsapp: whatsappNumber }),
                    contentType: 'application/json',
                    success: function(response) {
                        showToast(`${response.message}`, `${response.class}`);
                    },
                    error: function() {
                        showToast('Failed to send OTP. Please try again.', 'error');
                    },
                    complete: function() {
                        $button.text('Sent');
                        $button.text('OTP').prop('disabled', false).removeClass('loading');
                    }
                });
            } else {
                showToast('Please enter a valid WhatsApp number.', 'info');
            }
        });

        $('#birthdayForm').on('submit', function(event) {
            event.preventDefault();
            const $form = $(this);
            const $submitButton = $('#submitButton');
            const formData = new FormData(this);
            formData.append('_token', "{{ csrf_token() }}")
            console.log(formData)

            $submitButton.text('Hang on...').prop('disabled', true).addClass('loading');

            $.ajax({
                url: "{{ route('save') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    showToast(`${response.message}`, `${response.class}`);
                    $form.trigger('reset');
                },
                error: function() {
                    showToast('Failed to submit the form. Please try again.', 'error');
                },
                complete: function() {
                    $submitButton.text('Submit').prop('disabled', false).removeClass('loading');
                }
            });
        });

        function showToast(message, type) {
            const toast = $('#toast');
            toast.removeClass('toast-success toast-error toast-info');
            if (type === 'success') {
                toast.addClass('toast-success');
            } else if (type === 'error') {
                toast.addClass('toast-error');
            } else {
                toast.addClass('toast-info');
            }
            toast.text(message).addClass('show');
            setTimeout(() => {
                toast.removeClass('show');
            }, 3000);
        }
    </script>
</body>
</html>
