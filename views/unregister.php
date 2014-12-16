<?php
/**
* @file unregister.php
* @brief Template per la disiscrizione dalla newsletter
*
* Variabili disponibili:
* - **section_id**: attributo id del tag section
* - **title**: titolo della vista
* - **message**: messaggio
* - **form**: html form
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
    <p><?= $message ?></p>
    <?= $form ?>
</section>
<? // @endcond ?>
