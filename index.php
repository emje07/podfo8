<?php
// Initialize variables with empty strings or defaults to avoid undefined variable warnings
$program_name = "";
$program = $date_of_payout = $payout_venue = $target_client = $paid_client = $amount_disbursed = $assistance_type = $partner = $prepared_by = $position = $percentage = $sector = "";
$uploaded_images = [];
$signature_data = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $program = $_POST['program'];

    // Program names corresponding to the selected option
    if ($program == "AICS") {
        $program_name = "Assistance to Individuals in Crisis Situation (AICS)";
    } elseif ($program == "AKAP") {
        $program_name = "Ayuda sa Kapos ang Kita Program (AKAP)";
    }

    $date_of_payout = $_POST['date_of_payout'];
    $payout_venue = $_POST['payout_venue'];
    $sector = $_POST['sector'];
    $target_client = $_POST['target_client'];
    $paid_client = $_POST['paid_client'];
    $amount_disbursed = $_POST['amount_disbursed'];
    $partner = $_POST['partner'];

    // Calculate the percentage if valid data is present
    if ($target_client > 0) {
        $percentage = ($paid_client / $target_client) * 100;
    } else {
        $percentage = 0;
    }

    $assistance_type = $_POST['assistance_type'];
    $prepared_by = $_POST['prepared_by'];
    $position = $_POST['position'];

    // Handle image uploads
    $upload_dir = "uploads/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Handle each photo upload
    for ($i = 1; $i <= 3; $i++) {
        if (isset($_FILES['photo' . $i]) && $_FILES['photo' . $i]['error'] == 0) {
            $filename = basename($_FILES['photo' . $i]['name']);
            $target_file = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['photo' . $i]['tmp_name'], $target_file)) {
                $uploaded_images[] = $target_file; // Store the uploaded image path
            }
        }
    }

    // Handle the signature data
    if (isset($_POST['signature_data'])) {
        $signature_data = $_POST['signature_data']; // Capture the signature data (Base64 image)
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assistance Program Form</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <style>
        .photo-column img {
            width: 33%;
            height: auto;
            margin-bottom: 10px;
        }

        #signature-pad {
            border: 1px solid #ccc;
            width: 100%;
            height: 200px;
        }

        .signature-preview {
            padding-left: 30px;
            margin-left: 30px;
        }

        @media print {
            .input-column {
                display: none;
            }

            .output-column {
                width: 100%;
                margin: 0;
                padding: 10px;
            }

            button {
                display: none;
            }

            .text-center-print {
                text-align: center;
            }

            .partner-right {
                text-align: right;
            }

            @page {
                size: A4 landscape;
                margin: 10mm;
            }

            body {
                font-size: 12pt;
                line-height: 1.5;
            }

            .form-row-print {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
            }

            .form-group-print {
                width: 48%;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- First column (input) - 20% width -->
            <div class="col-md-2 input-column">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Assistance Program Form</h4>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="program">Program:</label>
                                <select name="program" id="program" class="form-control">
                                    <option value="AICS" <?php if ($program == "AICS") echo "selected"; ?>>Assistance to Individuals in Crisis Situation (AICS)</option>
                                    <option value="AKAP" <?php if ($program == "AKAP") echo "selected"; ?>>Ayuda sa Kapos ang Kita Program (AKAP)</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="date_of_payout">Date of Payout:</label>
                                <input type="date" name="date_of_payout" value="<?php echo htmlspecialchars($date_of_payout); ?>" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="payout_venue">Payout Venue:</label>
                                <input type="text" name="payout_venue" value="<?php echo htmlspecialchars($payout_venue); ?>" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="sector">Sector:</label>
                                <select name="sector" id="sector" class="form-control" required>
                                    <option value="Children in Need of Special Protection" <?php if ($sector == "Children in Need of Special Protection") echo "selected"; ?>>Children in Need of Special Protection</option>
                                    <option value="Family Heads and Other Needy Adult" <?php if ($sector == "Family Heads and Other Needy Adult") echo "selected"; ?>>Family Heads and Other Needy Adult</option>
                                    <option value="Persons with Disabilities" <?php if ($sector == "Persons with Disabilities") echo "selected"; ?>>Persons with Disabilities</option>
                                    <option value="Senior Citizens" <?php if ($sector == "Senior Citizens") echo "selected"; ?>>Senior Citizens</option>
                                    <option value="Persons Living with HIV" <?php if ($sector == "Persons Living with HIV") echo "selected"; ?>>Persons Living with HIV</option>
                                    <option value="Women" <?php if ($sector == "Women") echo "selected"; ?>>Women</option>
                                    <option value="Youth" <?php if ($sector == "Youth") echo "selected"; ?>>Youth</option>
                                    <option value="Persons with Special Needs" <?php if ($sector == "Persons with Special Needs") echo "selected"; ?>>Persons with Special Needs</option>
                                    <option value="Various" <?php if ($sector == "Various") echo "selected"; ?>>Various</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="target_client">Target Client:</label>
                                <input type="number" name="target_client" value="<?php echo htmlspecialchars($target_client); ?>" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="paid_client">Paid Client:</label>
                                <input type="number" name="paid_client" value="<?php echo htmlspecialchars($paid_client); ?>" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="amount_disbursed">Amount Disbursed (PHP):</label>
                                <input type="number" name="amount_disbursed" value="<?php echo htmlspecialchars($amount_disbursed); ?>" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="partner">Partner:</label>
                                <input type="text" name="partner" value="<?php echo htmlspecialchars($partner); ?>" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="assistance_type">Type of Assistance:</label>
                                <select name="assistance_type" id="assistance_type" class="form-control">
                                    <option value="Funeral Assistance" <?php if ($assistance_type == "Funeral Assistance") echo "selected"; ?>>Funeral Assistance</option>
                                    <option value="Medical Assistance" <?php if ($assistance_type == "Medical Assistance") echo "selected"; ?>>Medical Assistance</option>
                                    <option value="Transportation Assistance" <?php if ($assistance_type == "Transportation Assistance") echo "selected"; ?>>Transportation Assistance</option>
                                    <option value="Educational Assistance" <?php if ($assistance_type == "Educational Assistance") echo "selected"; ?>>Educational Assistance</option>
                                    <option value="Food Assistance" <?php if ($assistance_type == "Food Assistance") echo "selected"; ?>>Food Assistance</option>
                                    <option value="Cash Relief Assistance" <?php if ($assistance_type == "Cash Relief Assistance") echo "selected"; ?>>Cash Relief Assistance</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="prepared_by">Prepared By:</label>
                                <input type="text" name="prepared_by" value="<?php echo htmlspecialchars($prepared_by); ?>" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="position">Position:</label>
                                <input type="text" name="position" value="<?php echo htmlspecialchars($position); ?>" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="photo1">Photo 1 (Upload):</label>
                                <input type="file" name="photo1" accept="image/*" class="form-control-file" required>
                            </div>

                            <div class="form-group">
                                <label for="photo2">Photo 2 (Upload):</label>
                                <input type="file" name="photo2" accept="image/*" class="form-control-file" required>
                            </div>

                            <div class="form-group">
                                <label for="photo3">Photo 3 (Upload):</label>
                                <input type="file" name="photo3" accept="image/*" class="form-control-file" required>
                            </div>

                            <div class="form-group">
                                <label for="signature">Signature (Draw):</label>
                                <canvas id="signature-pad" width="400" height="150"></canvas>
                                <button type="button" id="clear-signature" class="btn btn-secondary mt-2">Clear</button>
                                <input type="hidden" name="signature_data" id="signature_data">
                                <button type="button" id="save-signature" class="btn btn-primary mt-2">Save</button>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">Submit</button>
                        </form>
                    </div>
                </div>
                <button onclick="printPage()" class="btn btn-success">Print</button>
            </div>

            <!-- Second column (output) - 80% width -->
            <div class="col-md-10 output-column">
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title text-center-print"><?php echo htmlspecialchars($program_name); ?></h4>
                            <p class="text-center-print"><strong>Date of Payout:</strong> <?php echo htmlspecialchars($date_of_payout); ?></p>
                            <p class="text-center-print"><strong>Payout Venue:</strong> <?php echo htmlspecialchars($payout_venue); ?></p>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Sector</th>
                                        <th>Target</th>
                                        <th>Paid</th>
                                        <th>Amount Disbursed</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?php echo htmlspecialchars($sector); ?></td>
                                        <td><?php echo htmlspecialchars($target_client); ?></td>
                                        <td><?php echo htmlspecialchars($paid_client); ?></td>
                                        <td>PHP <?php echo number_format($amount_disbursed, 2); ?></td>
                                        <td><?php echo number_format($percentage, 2); ?>%</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="photo-column">
                                <?php foreach ($uploaded_images as $image): ?>
                                    <img src="<?php echo $image; ?>" alt="Uploaded Photo">
                                <?php endforeach; ?>
                            </div>

                            <div class="signature-preview">
                                <!-- The signature will be displayed here once saved -->
                            </div>

                            <p><strong>Prepared By:</strong> <?php echo htmlspecialchars($prepared_by); ?></p>
                            <p><strong>Approved By:</strong> <?php echo htmlspecialchars($position); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        const canvas = document.getElementById('signature-pad');
        const ctx = canvas.getContext('2d');
        const clearBtn = document.getElementById('clear-signature');
        const saveBtn = document.getElementById('save-signature');
        const signatureInput = document.getElementById('signature_data');

        let isDrawing = false;

        canvas.addEventListener('mousedown', (e) => {
            isDrawing = true;
            ctx.beginPath();
            ctx.moveTo(e.offsetX, e.offsetY);
        });

        canvas.addEventListener('mousemove', (e) => {
            if (isDrawing) {
                ctx.lineTo(e.offsetX, e.offsetY);
                ctx.stroke();
            }
        });

        canvas.addEventListener('mouseup', () => {
            isDrawing = false;
            signatureInput.value = canvas.toDataURL(); // Capture the signature image in base64 format
        });

        canvas.addEventListener('mouseleave', () => {
            isDrawing = false;
        });

        clearBtn.addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height); // Clear the canvas
            signatureInput.value = ''; // Reset the signature data
        });

        saveBtn.addEventListener('click', () => {
            // Get the base64 encoded signature image
            const signatureImage = signatureInput.value;

            // Check if there is a signature and display it in column 2
            if (signatureImage) {
                const signaturePreview = document.querySelector('.signature-preview');

                // Clear the existing content of the signature preview
                signaturePreview.innerHTML = '';

                // Create an img element and set the src to the signature base64 data
                const signatureImg = document.createElement('img');
                signatureImg.src = signatureImage;
                signatureImg.alt = "Signature";
                signatureImg.style.width = "200px"; // Optional: Adjust the size of the signature

                // Append the image to the output column's signature preview
                signaturePreview.appendChild(signatureImg);
            }
        });

        function printPage() {
            // Hide buttons before printing
            document.querySelector('button').style.display = 'none';
            window.print();
        }
    </script>
</body>
</html>
