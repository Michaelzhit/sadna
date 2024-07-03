<?php
session_start();

// הפניה לעמוד התחברות אם המשתמש לא מחובר
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Unknown User';

// כלול את PayPal SDK ו-Composers autoload
require '../../vendor/autoload.php';
include '../config.php';

// הגדרות PayPal
$client_id = 'Aeh9_E_hfVIAbjx3nyc4vVFa0qHr4_GOEeFZ6bumvFMoGQV-mqcmAsKRdWx0jndFD_Vro10MekjDI_Ln';
$client_secret = 'ENem-KBa5HvB2OU9RFmOVPjHaLr4juyziB9UFujAk8YdfpIIMupWxUP30RnG3K1vc8911QCW9TByKMl4';
$settings = [
    'mode' => 'sandbox', 
    'http.ConnectionTimeOut' => 30,
    'log.LogEnabled' => true,
    'log.FileName' => 'PayPal.log',
    'log.LogLevel' => 'FINE'
];

// יצירת קונטקסט PayPal API
$apiContext = new \PayPal\Rest\ApiContext(
    new \PayPal\Auth\OAuthTokenCredential(
        $client_id,
        $client_secret
    )
);

$apiContext->setConfig($settings);

use PayPal\Api\Payment;
use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\InputFields;
use PayPal\Api\WebProfile;
use PayPal\Api\Presentation;
use PayPal\Api\FlowConfig;

$fromUserId = $_SESSION['user_id'];
$toUserId = isset($_POST['to_user_id']) ? intval($_POST['to_user_id']) : 0;
$amountValue = isset($_POST['amount']) ? $_POST['amount'] : '0.01'; // ברירת מחדל ל-0.01 לשם בדיקה

// קבל את כתובת העמוד הנוכחית מהבקשה
$returnUrl = isset($_POST['return_url']) ? urldecode($_POST['return_url']) : 'https://example.com/success';

// הוסף לוגים לבדיקת משתנים
error_log("From User ID: $fromUserId");
error_log("To User ID: $toUserId");
error_log("Amount: $amountValue");
error_log("Return URL: $returnUrl");

try {
    // יצירת אובייקט תשלום חדש
    $payment = new Payment();
    $payment->setIntent('sale');

    // הגדרת פרטי משלם
    $payer = new Payer();
    $payer->setPaymentMethod('paypal');

    // הגדרת סכום טרנסקציה
    $amount = new Amount();
    $amount->setCurrency('ILS');
    $amount->setTotal($amountValue);

    // הגדרת פרטי טרנסקציה
    $transaction = new Transaction();
    $transaction->setAmount($amount);
    $transaction->setDescription("Payment by $userName"); // כלול את שם המשתמש בתיאור
    $transaction->setInvoiceNumber(uniqid()); // הוסף מספר חשבונית ייחודי אם נדרש

    // הגדרת כתובות הפניה מחדש
    $redirectUrls = new RedirectUrls();
    $redirectUrls->setReturnUrl($returnUrl)
                 ->setCancelUrl($returnUrl);

    // הגדרת שדות קלט למזעור מידע בעמוד PayPal
    $inputFields = new InputFields();
    $inputFields->setNoShipping(1); // לא תוצג כתובת משלוח

    // הגדרת הגדרות הצגה
    $presentation = new Presentation();
    $presentation->setBrandName("Your Company");

    // הגדרת תצורת זרימה לשימוש בעמוד החיוב
    $flowConfig = new FlowConfig();
    $flowConfig->setLandingPageType("Billing");

    // יצירת פרופיל אינטרנט והגדרתו לתשלום
    $webProfile = new WebProfile();
    $webProfile->setName("Your Company " . uniqid())
               ->setFlowConfig($flowConfig)
               ->setInputFields($inputFields)
               ->setPresentation($presentation)
               ->setTemporary(true); // פרופיל זמני עבור העסקה

    $createProfileResponse = $webProfile->create($apiContext);
    $profileId = $createProfileResponse->getId();
    error_log("Web Profile ID: $profileId"); // לוג של Web Profile ID

    $payment->setExperienceProfileId($profileId);

    // הוסף משלם, טרנסקציות, וכתובות הפניה מחדש לאובייקט התשלום
    $payment->setPayer($payer);
    $payment->setTransactions([$transaction]);
    $payment->setRedirectUrls($redirectUrls);

    // יצירת התשלום
    $payment->create($apiContext);

    // קבלת כתובת ההפניה של PayPal
    $approvalUrl = $payment->getApprovalLink();
    error_log("Approval URL: $approvalUrl"); // לוג של Approval URL

    // הוספת קישור התשלום כהודעה במסד הנתונים
    $message = "Please proceed with the payment by clicking the following link: <a href='$approvalUrl'>Pay Now</a>";
    $messageType = 'payment';

    $query = "INSERT INTO Messages (from_user_id, to_user_id, content, timestamp, type) VALUES (?, ?, ?, NOW(), ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiss", $fromUserId, $toUserId, $message, $messageType);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Payment link sent successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to send payment link.']);
    }

    $stmt->close();
} catch (Exception $ex) {
    error_log("Error: " . $ex->getMessage()); // לוג של השגיאה
    echo json_encode(['status' => 'error', 'message' => 'Error creating payment link.', 'error' => $ex->getMessage()]);
    exit(1);
}

$conn->close();
?>
