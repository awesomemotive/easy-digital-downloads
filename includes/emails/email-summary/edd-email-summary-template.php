<!DOCTYPE html>
	<html>
		<head>
			<title><?php echo esc_html( $this->get_email_subject() ); ?></title>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<!--[if !mso]><!-->
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<!--<![endif]-->
			<?php require 'template-parts/email-styles.php'; ?>
		</head>


		<body style="margin: 0px auto; max-width: 450px;">
			<!-- PREVIEW TEXT -->
			<?php require 'template-parts/preview-text.php'; ?>

			<!-- HEADER HOLDER -->
			<?php require 'template-parts/header.php'; ?>

			<div class="email-container" style="max-width: 450px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, avenir next, avenir, segoe ui, helvetica neue, helvetica, Cantarell, Ubuntu, roboto, noto, arial, sans-serif; color: #1F2937;">


				<!-- MAIN EMAIL CONTENT HOLDER -->

				<div class="content-box" style="background: #FFF;">

					<div class="content-holder" style="padding: 22px 31px 0px 31px;">
						<!-- SITE INFO -->
						<?php require 'template-parts/site-info.php'; ?>

						<!-- DATA LISTING -->
						<?php require 'template-parts/data-listing.php'; ?>

						<hr style="border: 0.2px solid #E5E7EB; display: block;">

						<?php require 'template-parts/fee-info.php'; ?>

						<!-- TABLE DATA -->
						<?php require 'template-parts/top-products.php'; ?>

						<a href="<?php echo esc_attr( $view_more_url ); ?>" style="color: #2794DA; margin-top: 15px; margin-bottom: 15px; font-weight: 400; font-size: 14px; text-decoration-line: underline; display: inline-block; text-decoration: none;">
							<?php echo esc_html( __( 'View Full Report', 'easy-digital-downloads' ) ); ?>
						</a>

					</div>

				</div>
				<!-- /.content-box -->


			</div>
			<!-- /.email-container -->


			<!-- PRO-TIP SECTION -->
			<?php require 'template-parts/pro-tips.php'; ?>
			<!-- /.pro-tip-blurb -->

		</body>
	</html>
