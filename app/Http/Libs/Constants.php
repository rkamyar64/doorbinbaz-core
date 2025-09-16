<?php

namespace App\Http\Libs;

class Constants
{

    const  ERROR_INVALID_INPUT_FORMAT = "INVALID INPUT FORMAT";
    const SYNC_TO_ONLINE_TOKEN    = "ASHDJKhajkshdkjhakjshdjkahjksdhjkhakjshdjkhajkhsdjkhajkhsdkjh";
    const ERROR_SYNC_TO_ONLINE_TOKEN    = "ERROR SYNC TOKEN";
    const SMS_API_KEY             = "525655e7f16e34c26ffa51a6";
    const SMS_SECRET_KEY          = "29Or1365@!jetQuery";
    const SMS_Template_Id         = "1489";
    const SENT_SMS_OTP            = "کد ورود به پنل شما: ";
    const SENT_SMS_REQUEST_VISA            = "کد تایید درخواست شما: ";
    const SUBMIT_REGISTER_VISA            = "درخواست خدمات ویزای X با کد پیگیری Z با موفقیت ثبت شد. کارشناسان ما در اسرع وقت با شما تماس خواهند گرفت.".PHP_EOL."ویزالند";

    const OTP_TOKEN_LENGTH = 4;
    const TOKEN_KEY = "Visa land management Password";

    const SUCCESS = "Success";
    const SUCCESS_STORE = "Store Successful";
    const SUCCESS_UPDATE = "Update Successful";
    const SUCCESS_DELETE = "Delete Successful";

    const SUCCESS_LOGIN = "Login was successful";
    const SUCCESS_OTP_TOKEN_SENT = "SMS containing the password has been sent to you";

    const ERROR = "Error";
    const ERROR_UPDATE = "UPDATE ERROR";
    const ERROR_STORE = "STORE ERROR";
    const ERROR_DELETE = "DELETE ERROR";

    const ERROR_CUSTOMER_EXIST = "This customer has already been registered.";
    const ERROR_USER_NOT_FOUND = 'User not found';
    const ERROR_INVALID_PASSWORD = 'Invalid password';
    const ERROR_INVALID_INPUT_DATA =  "Invalid Input Data";
    const ERROR_MESSAGE_SENT_WAITING = "A message has been sent to you. Please wait.";



}
