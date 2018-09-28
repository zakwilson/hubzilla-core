[h2]content_security_policy[/h2]

Called to modify CSP settings prior to the output of the Content-Security-Policy header.

This hook permits addons to modify the content-security-policy if necessary to allow loading of foreign js libraries or css styles.

[code]
if(App::$config['system']['content_security_policy']) {
        $cspsettings = Array (
                'script-src' => Array ("'self'","'unsafe-inline'","'unsafe-eval'"),
                'style-src' => Array ("'self'","'unsafe-inline'")
        );
        call_hooks('content_security_policy',$cspsettings);

        // Legitimate CSP directives (cxref: https://content-security-policy.com/)
        $validcspdirectives=Array(
                "default-src", "script-src", "style-src",
                "img-src", "connect-src", "font-src",
                "object-src", "media-src", 'frame-src',
                'sandbox', 'report-uri', 'child-src',
                'form-action', 'frame-ancestors', 'plugin-types'
        );
        $cspheader = "Content-Security-Policy:";
        foreach ($cspsettings as $cspdirective => $csp) {
                if (!in_array($cspdirective,$validcspdirectives)) {
                        logger("INVALID CSP DIRECTIVE: ".$cspdirective,LOGGER_DEBUG);
                        continue;
                }
                $cspsettingsarray=array_unique($cspsettings[$cspdirective]);
                $cspsetpolicy = implode(' ',$cspsettingsarray);
                if ($cspsetpolicy) {
                        $cspheader .= " ".$cspdirective." ".$cspsetpolicy.";";
                }
        }
        header($cspheader);
}
[/code]

see: boot.php
