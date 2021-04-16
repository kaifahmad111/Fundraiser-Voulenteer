<?php
echo "Hello 123";
?>
<?php
get_header();
//For Validation

if(isset($_POST['submitted'])) {

    if(trim($_POST['contactName']) != '' && trim($_POST['email']) != '' && trim($_POST['comments']) != '') {
        $fname = trim($_POST['firstName']);
        $lname = trim($_POST['lastName']);
        $email = trim($_POST['email']);
        $comments = trim($_POST['comments']);
        $country= trim($_POST['country']);
        $state= trim($_POST['state']);
        echo "$fname,$email,$country,$state";
        $tags = array("employee" , "slaves");
        $new_post = array(
            'post_title'    => "$name",
            'post_author'    => $email,
            'post_parent'   => $state,
            'post_content'  => $comments,
            'tags_input'    => array($tags),
            'post_status'   => 'publish',           // Choose: publish, preview, future, draft, etc.
            'post_type'     => "voulunteer"  // Use a custom post type if you want to
        );
    //save the new post and return its ID
    $pid = wp_insert_post($new_post); 
    echo "This is the pid:".$pid;
    update_post_meta($pid,"meta-box-country",$country);
    update_post_meta($pid,"meta-box-state",$state);    
}
}

//For Validation
else {
    get_header()                                                                                    
 ?>

<div id="container">
    <div id="content">
        <?php the_post() ?>
        <div id="post-<?php the_ID() ?>" class="post">
            <h2><?php echo the_ID();?></h2>

            <form action="<?php the_permalink(); ?>" id="contactForm" method="post">
                <ul>
                    <li>
                        <label for="firstName">First Name:</label>
                        <input type="text" name="firstName" id="firstName" value="" />
                    </li>
                    <li>
                        <label for="lastName">Last Name:</label>
                        <input type="text" name="lastName" id="lastName" value="" />
                    </li>
                    <li>
                        <label for="lastName">Upload Multiple files: </label>
                        <input type="file" name="multiple_files" id="multiple_files" multiple="multiple"/>
                    </li>
                    <li>
                        <label for="email">Email</label>
                        <input type="text" name="email" id="email" value="" />
                    </li>
                    <li>
                        <label for="country">Country</label>
                        <input type="text" name="country" id="country" value="" />
                    </li>
                    <li>
                        <label for="state">State</label>
                        <input type="text" name="state" id="state" value="" />
                    </li>
                    <li>
                        <label for="commentsText">Message:</label>
                        <textarea name="comments" id="commentsText" rows="20" cols="30"></textarea>
                    </li>
                    <li>
                        <button type="submit">Submit</button>
                    </li>
                </ul>
                <input type="hidden" name="submitted" id="submitted" value="true" />
            </form>

            <div class="entry-content">
            </div>
        </div>
    </div>
</div>

<?php
}
get_sidebar() ?>
<?php get_footer() ?>