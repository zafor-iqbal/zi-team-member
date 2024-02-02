<?php get_header(); ?>

<div class="container">
    <div class="row">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <div class="col-md-6">
            <div class="team-member-single">
                <?php if (has_post_thumbnail()) : ?>
                <div class="team-member-thumbnail">
                    <?php the_post_thumbnail(); ?>
                </div>
                <?php endif; ?>
                <?php the_title('<h2>', '</h2>'); ?>
                <?php 
                // Output the position using a custom field (update 'position' to your actual meta key)
                $position = get_post_meta( get_the_ID(), '_team_member_position', true );
                if ( $position ) {
                    echo '<p>' . esc_html( $position ) . '</p>';
                }
                ?>
                <div class="team-member-content">
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <div class="row">
        <div class="col-12" style="justify-content: center;">
            <?php
            // Pagination
            the_posts_pagination(array(
                'mid_size'  => 2,
                'prev_text' => __('Back', 'textdomain'),
                'next_text' => __('Next', 'textdomain'),
            ));
            ?>
        </div>
    </div>

    <?php else : ?>
    <p><?php _e('Sorry, no team members found.', 'textdomain'); ?></p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>