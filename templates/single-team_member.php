<?php

get_header();

while ( have_posts() ) :
    the_post();
    ?>
<div class="team-member-single">
    <?php 
    if ( has_post_thumbnail() ) {
        the_post_thumbnail();
    }
    the_title('<h1>', '</h1>');
    ?>
    <?php 
    // Output the position using a custom field (update 'position' to your actual meta key)
    $position = get_post_meta( get_the_ID(), '_team_member_position', true );
    if ( $position ) {
        echo '<p>' . esc_html( $position ) . '</p>';
    }
    ?>
    <div class="single-member-bio">
        <?php 
        the_content();
        ?>
    </div>

</div>
<?php 

endwhile;

get_footer();
?>