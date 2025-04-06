@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <h2 class="text-center text-primary mb-4">🗳️ Vote Now</h2>

        <form id="vote-form">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                    <small class="text-danger email-error d-none"></small>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Member ID</label>
                    <input type="text" name="member_id" class="form-control" required>
                </div>
            </div>

            <hr>

            <h5>🗳️ Vote for Candidates</h5>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Person 1 (Yes/No)</label>
                    <select name="person_1" class="form-control" required>
                        <option value="">Select</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Person 2 (Yes/No)</label>
                    <select name="person_2" class="form-control" required>
                        <option value="">Select</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <label class="form-label">Select Either Person 3 or Person 4</label>
                    <select name="selected_person" class="form-control" required>
                        <option value="">Select</option>
                        <option value="person_3">Person 3</option>
                        <option value="person_4">Person 4</option>
                    </select>
                </div>
            </div>

            <hr>

            <h5>🔐 OTP Verification</h5>

            <div class="row mt-3">
                <div class="col-md-6">
                    <button type="button" id="sendOtpBtn" class="btn btn-primary w-100" onclick="sendOtp()">📩 Send OTP</button>
                </div>
                <div class="col-md-6 hideElement" style="display: none;">
                    <input type="text" id="otp" class="form-control" placeholder="Enter OTP" required>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6 hideElement" style="display: none;">
                    <button type="button" id="verifyOtpBtn" class="btn btn-success w-100" onclick="verifyOtp()">✅ Verify OTP</button>
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-dark w-100">🗳️ Submit Vote</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>

<script>
$(document).ready(function () {
    checkOtpTimer(); // Check OTP timer on page load

    $("#vote-form").validate({
        rules: {
            full_name: "required",
            email: {
                required: true,
                email: true
            },
            phone: {
                required: true,
                digits: true,
                minlength: 10,
                maxlength: 10
            },
            member_id: "required",
            person_1: "required",
            person_2: "required",
            selected_person: "required"
        },
        messages: {
            full_name: "Please enter your full name",
            email: "Enter a valid email",
            phone: {
                required: "Enter your phone number",
                digits: "Only numeric values are allowed",
                minlength: "Phone number must be exactly 10 digits",
                maxlength: "Phone number must be exactly 10 digits"
            },
            member_id: "Enter your member ID",
            person_1: "Select Yes or No for Person 1",
            person_2: "Select Yes or No for Person 2",
            selected_person: "Select either Person 3 or Person 4"
        },
        errorClass: "text-danger",
        errorElement: "small",
        highlight: function (element) {
            $(element).addClass("is-invalid");
        },
        unhighlight: function (element) {
            $(element).removeClass("is-invalid");
        },
        submitHandler: function (form) {
            submitVote();
        }
    });

    $("#email").on("blur", function () {
        let email = $(this).val().trim();
        if (!email) return;

        fetch('/check-user', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                $("input[name='member_id']").val(data.member_id).prop("readonly", true);
            } else {
                $("input[name='member_id']").val("").prop("readonly", false);
            }
        });
    });
});
let interval;
// Function to send OTP
function sendOtp() {
    let email = $("#email").val();
    if (!email) {
        $("#email").addClass("is-invalid");
        $(".email-error").removeClass("d-none").text("Please enter your email");
        return;
    } else {
        $("#email").removeClass("is-invalid");
        $(".email-error").addClass("d-none").text("");
    }

    fetch('/send-otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);

        if (data.status) {
            $(".hideElement").fadeIn();
            let expiryTime = new Date().getTime() + 60000; // 60 seconds from now
            localStorage.setItem('otpExpiry', expiryTime);
            disableOtpButton(60);
        }
    });
}

// Function to disable OTP button and enable OTP input and Verify button
function disableOtpButton(seconds) {
    $("#sendOtpBtn").prop("disabled", true).text(`Wait ${seconds} sec`);
    $(".hideElement").fadeIn();

    let countdown = seconds;
    interval = setInterval(function () {
        countdown--;
        $("#sendOtpBtn").text(`Wait ${countdown} sec`);

        if (countdown <= 0) {
            clearInterval(interval);
            $("#sendOtpBtn").prop("disabled", false).text("📩 Send OTP");
            localStorage.removeItem('otpExpiry'); // Clear expiry from localStorage
        }
    }, 1000);
}

// Function to check OTP timer on page reload
function checkOtpTimer() {
    let expiryTime = localStorage.getItem('otpExpiry');
    if (expiryTime) {
        let currentTime = new Date().getTime();
        let remainingTime = Math.ceil((expiryTime - currentTime) / 1000);

        if (remainingTime > 0) {
            disableOtpButton(remainingTime);
        } else {
            localStorage.removeItem('otpExpiry'); // Remove expired timer
        }
    }
}

// Function to verify OTP
function verifyOtp() {
    let email = $("#email").val();
    let otp = $("#otp").val();

    if (!otp) {
        alert("Please enter OTP!");
        return;
    }

    fetch('/verify-otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ email: email, otp: otp })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.status) {
            $("#verifyOtpBtn").prop("disabled", true).text("✅ Verified");
        }
    });
}

// Function to submit vote
function submitVote() {
    let formData = new FormData($("#vote-form")[0]);
    fetch('/submit-vote', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    body: formData
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.status) {
            $(".hideElement").fadeOut();
            localStorage.removeItem('otpExpiry');

            if (interval) {
                clearInterval(interval); // Stop the countdown
                interval = null;
            }
            $("#sendOtpBtn").prop("disabled", false).text("📩 Send OTP");
            $("#vote-form")[0].reset();
        }
    })
    .catch(error => console.error('Error:', error));
}

</script>

@endsection
