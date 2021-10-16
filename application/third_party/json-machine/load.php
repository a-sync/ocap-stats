<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once('src/Exception/JsonMachineException.php');
require_once('src/Exception/PathNotFoundException.php');
require_once('src/Exception/SyntaxError.php');
require_once('src/Exception/UnexpectedEndSyntaxErrorException.php');

require_once('src/JsonDecoder/Decoder.php');
require_once('src/JsonDecoder/DecodingError.php');
require_once('src/JsonDecoder/DecodingResult.php');
require_once('src/JsonDecoder/ErrorWrappingDecoder.php');
require_once('src/JsonDecoder/JsonDecodingTrait.php');
require_once('src/JsonDecoder/ExtJsonDecoder.php');
require_once('src/JsonDecoder/PassThruDecoder.php');

require_once('src/functions.php');
require_once('src/PositionAware.php');
require_once('src/StreamChunks.php');
require_once('src/StringChunks.php');
require_once('src/FileChunks.php');
require_once('src/Lexer.php');
require_once('src/Parser.php');
require_once('src/JsonMachine.php');
