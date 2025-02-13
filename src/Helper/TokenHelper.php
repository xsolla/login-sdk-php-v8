<?php

namespace Xsolla\LoginSdk\Helper;

class TokenHelper
{
    const HEADER_ALG = 'alg';

    const CLAIM_KID = 'kid';
    const CLAIM_PROJECT_ID = 'xsolla_login_project_id';
    const CLAIM_ISSUER = 'iss';
    const CLAIM_JTI = 'jti';
    const CLAIM_EXP = 'exp';
}
