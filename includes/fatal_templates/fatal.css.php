<?php
    // Inherit from including context:
    if (!isset($shouldRenderForTwig))
    {
        $shouldRenderForTwig = false;
    }
?>
<style>
<?php if (!$shouldRenderForTwig): ?>
html, body
{
    min-height: 100vh;
}

body
{
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 13px;
    background-color: #f5f8fa !important;
    display: flex;
    justify-content: center;
    align-items: center;
}

body, input, button, textarea, select
{
    font-family: Arial, Helvetica, sans-serif;
}

<?php endif ?>

#rehike-fatal-error
{
    width: 800px;
    padding: 15px;
    margin: 0 auto;
    background: #fff;
    border: 1px solid #e1e8ed;
    border-radius: 5px;
    box-sizing: border-box;
}

.header > *
{
    vertical-align: middle;
}

.header h1
{
    display: inline;
}

.section-header
{
    font-weight: 500;
}

.fatal-button
{
    background-color:#ccd6dd;
    background-repeat:no-repeat;
    border:1px solid #e1e8ed;
    border-radius:4px;
    color: #292f33;
    cursor:pointer;
    display:inline-block;
    font-size:13px;
    font-weight:bold;
    line-height:normal;
    padding:8px 16px;
    position:relative;
    
    background-color:#f5f8fa;
    background-image:linear-gradient(#fff,#f5f8fa);
    -ms-filter:"progid:DXImageTransform.Microsoft.gradient(startColorstr=#fff, endColorstr=#f5f8fa)"
}

.fatal-button:hover
{
    color: #292f33;
    text-decoration: none;
    background-color: #e1e8ed;
    background-image: linear-gradient(#fff,#e1e8ed);
    -ms-filter: "progid:DXImageTransform.Microsoft.gradient(enabled=false)";
    border-color: #e1e8ed;
}

.fatal-button:active
{
    color: #292f33;
    background: #e1e8ed;
    border-color: #ccd6dd;
    box-shadow: inset 0 1px 4px rgba(0,0,0,0.2);
}

.failed-request-info .section-title
{
    font-weight: 500;
}

.failed-request-text
{
    font-family: Consolas, monospace;
}

<?php
// Error log text styling:
?>
ul.fatal-error-info
{
    list-style: none;
}

.fatal-error-info .section-title
{
    font-weight: bold;
}

.fatal-error-info li
{
    margin-bottom: 3px;
}

.no-message
{
    color: #666;
    font-style: italic;
}

.exception-log
{
    word-wrap: break-word;
    white-space: pre-wrap;
    white-space: -moz-pre-wrap;
    white-space: -pre-wrap;
    white-space: -o-pre-wrap;
}

.exception_class
{
    font-weight: bold;
}

.at_text
{
    color: #666;
}

.class_name, .double_colon_operator
{
    color: #990080;
}

.method_name, .function_name
{
    font-style: italic;
    font-weight: bold;
    color: #99112c;
}

.file_line_parentheses, .file_line
{
    color: #666;
}

.args_type_object
{
    color: #8a2be2;
}

.args_type_string
{
    color: #008000;
}

.args_type_number
{
    color: #1f47e3;
}

.args_type_resource, .args_type_null, .args_type_unknown, .args_type_array
{
    color: #333;
}

</style>