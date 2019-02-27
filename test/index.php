<?php

// Load all the classes
$classesDir = dirname(dirname(__FILE__)) . '/src/';
foreach (array_merge(
    glob($classesDir . '*.php'),
    glob($classesDir . '**/*.php')
) as $filepath) {
    require $filepath;
}

$url = 'http://www.example.com/slug-1/slug-2?param-1=value-1&empty=&another-empty&param-2=value-2#element-id';
$urlEditor = new Nerbiz\UrlEditor\UrlEditor($url);
?>

<style>
    body {
        font-family: 'Arial', sans-serif;
    }

    pre {
        color: #2b65ec;
        font-size: 14px;
    }
</style>

<h1>UrlEditor test</h1>

<p>Initial URL:</p>
<pre><?php echo htmlentities($url); ?></pre>

<h3>URL base</h3>
<pre><?php echo htmlentities(var_export(
    $urlEditor->getBase()
, true)); ?></pre>

<h3>URL parts (using parse_url())</h3>
<pre><?php echo htmlentities(var_export(
    $urlEditor->getParts()
, true)); ?></pre>

<h3>Secure URL?</h3>
<pre><?php echo var_export(
    $urlEditor->isSecure()
, true); ?></pre>

<h3>Slugs</h3>
<pre><?php echo htmlentities(var_export(
    $urlEditor->getSlugs()->toArray()
, true)); ?></pre>

<h3>GET parameters</h3>
<pre><?php echo htmlentities(var_export(
    $urlEditor->getParameters()->toArray()
, true)); ?></pre>

<h3>Fragment (anchor)</h3>
<pre><?php echo htmlentities(var_export(
    $urlEditor->getFragment()->toString()
, true)); ?></pre>

<h3>Change to a secure URL</h3>
<pre><?php echo htmlentities(var_export(
    $urlEditor->setIsSecure(true)->getFull()
, true)); ?></pre>

<h3>Add some slugs</h3>
<?php
$urlEditor->getSlugs()
    // Add at the end
    ->add('slug-4')
    // Add at an index
    ->addAt(2, 'slug-3')
    // Merge with existing
    ->mergeWith([
        'slug-2',
        'slug-3',
    ]);
?>
<pre><?php echo var_export(
    $urlEditor->getSlugs()->toArray()
, true); ?></pre>
<pre><?php echo htmlentities(var_export(
    $urlEditor->getFull()
, true)); ?></pre>

<h3>Remove some slugs</h3>
<?php
$urlEditor->getSlugs()
    // Remove by name (only the first occurence)
    ->remove('slug-2')
    // Remove all occurences
    ->remove('slug-3', true)
    // Remove by index
    ->removeAt(0);
?>
<pre><?php echo var_export(
    $urlEditor->getSlugs()->toArray()
, true); ?></pre>
<pre><?php echo htmlentities(var_export(
    $urlEditor->getFull()
, true)); ?></pre>

<h3>Has slug 'slug-2'?</h3>
<pre><?php echo var_export(
    $urlEditor->getSlugs()->has('slug-2')
, true); ?></pre>

<h3>Add some GET parameters</h3>
<?php
$urlEditor->getParameters()
    // Add at the end
    ->add('param-3', 'value-3')
    // Merge with existing
    ->mergeWith([
        'param-4' => 'value-4',
        'param-5' => 'value-5',
    ]);
?>
<pre><?php echo var_export(
    $urlEditor->getParameters()->toArray()
, true); ?></pre>
<pre><?php echo htmlentities(var_export(
    $urlEditor->getFull()
, true)); ?></pre>

<h3>Remove some GET parameters</h3>
<?php
$urlEditor->getParameters()
    // Remove by key
    ->remove('empty')
    ->remove('param-1')
    ->remove('param-2')
    ->remove('param-5')
    // Remove by index
    ->removeAt(0);
?>
<pre><?php echo var_export(
    $urlEditor->getParameters()->toArray()
, true); ?></pre>
<pre><?php echo htmlentities(var_export(
    $urlEditor->getFull()
, true)); ?></pre>

<h3>Change the fragment (anchor)</h3>
<?php
$urlEditor->getFragment()->fromString('other-element-id');
?>
<pre><?php echo htmlentities(var_export(
    $urlEditor->getFull()
, true)); ?></pre>
