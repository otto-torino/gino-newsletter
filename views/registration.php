<?php
/**
* @file registration.php
* @ingroup newsletter
* @brief Template per la vista box di registrazione
*
* Variabili disponibili:
* - **section_id**: attributo id del tag section
* - **title**: titolo della vista
* - **form**: form di registrazione
* - **archive_url*: url dell'archivio newsletter
* - **msg*: messaggio conseguente all'azione di registrazione
*
* @version 0.1.0
* @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @author Marco Guidotti guidottim@gmail.com
* @author abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\Newsletter; ?>
<? //@cond no-doxygen ?>
<section id="<?= $section_id ?>">
<h1><?= $title ?></h1>
<?= $form ?>
<div class="separator"></div>
<p class="archive"><a href="<?= $archive_url ?>">archivio</a></p>
<? if($msg): ?>
<script>
    alert('<?= \Gino\jsVar($msg) ?>');
</script>
<? endif ?>
</section>
<? // @endcond ?>
