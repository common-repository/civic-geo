<?php
/**
*
* http://localhost:8099/?page_id=1192981
* https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/
* https://www.experiencesolutionsnow.com/form-1/?rep=Representive1&senator1=ChuckSchumer&senator2=Gilibrand
*/
$debug = false;
$continue = $lookup->getRedirect();
if($_REQUEST['congress_address']) {
    $address = wp_kses_data($_REQUEST['congress_address']);
}

global $post;
$page_id = (int)$post->ID;
?>

<form method="post" action="<?php echo esc_url($lookup->getUrl()) ?>"
    class="civic-lookup-form"
    id="find-legislators-form"
>

    <?php if( $htext ) { ?><p class="le_head"><?php echo wp_esc_html($htext) ?></p> <?php } ?>
    <input type="hidden" name="page_id" value="<?php echo (int)$page_id ?>">
    <fieldset id="user-details">
        <label for="congress_address">Address:</label>
        <input
            type="text"
            name="congress_address"
            id="congress_address<?php echo (int)$id ?>"
            placeholder="Your address"
            value="<?php echo wp_kses_data($address); ?>"
        />
        <input type="submit" value="Find Your Legislators" name="submit" class="submit button gform_button btn btn-primary civic-lookup-button civic-lookup-button-find" />
    </fieldset>
</form>

<?php
if($address) {
    $res = $lookup->lookupAddress($address);
    if($res->errors) {
        foreach($res->errors as $error) {
            ?><div class="alert"><?php echo esc_html($error)?> </div><?php
        }
    }
    $lookup->showResult();
    if($debug) {
        dump($res);
    }
    // dump($lookup);
    /**
     *
     */
    if($continue && !$lookup->errors) {
        $url = $continue."?rep=".$lookup->getRep()->getName();
        $url .= "&senator1=".$lookup->getSenator1()->getName();
        $url .= "&senator2=".$lookup->getSenator2()->getName();

        ?>
        <a href="<?php echo esc_url($url); ?>" class="button gform_button btn btn-primary civic-lookup-button civic-lookup-button-continue">
            Continue
        </a>
        <?php
    }
}
