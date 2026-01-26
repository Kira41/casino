<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$database = getDatabase();
$activePage = 'contact';
$casinoDirectory = fetchCasinoDirectory($database);
$pageTitle = 'Lugx Gaming Template - Contact Page';

include __DIR__ . '/partials/html-head.php';
include __DIR__ . '/partials/header.php';
?>

  <div class="page-heading header-text">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <h3>Contact Us</h3>
          <span class="breadcrumb"><a href="#">Home</a>  >  Contact Us</span>
        </div>
      </div>
    </div>
  </div>

  <div class="contact-page section">
    <div class="container">
      <div class="row">
        <div class="col-lg-6 align-self-center">
          <div class="left-text">
            <div class="section-heading">
              <h6>Contact Us</h6>
              <h2>Say Hello!</h2>
            </div>
            <p>LUGX Gaming Template is based on the latest Bootstrap 5 CSS framework. This template is provided by TemplateMo and it is suitable for showcasing all casinos ecommerce experiences. Feel free to use this for any purpose. Thank you.</p>
            <ul>
              <li><span>Address</span> Sunny Isles Beach, FL 33160, United States</li>
              <li><span>Phone</span> +123 456 7890</li>
              <li><span>Email</span> lugx@contact.com</li>
            </ul>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="right-content">
            <div class="row">
              <div class="col-lg-12">
                <div id="map">
                  <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d12469.776493332698!2d-80.14036379941481!3d25.907788681148624!2m3!1f357.26927939317244!2f20.870722720054623!3f0!3m2!1i1024!2i768!4f35!3m3!1m2!1s0x88d9add4b4ac788f%3A0xe77469d09480fcdb!2sSunny%20Isles%20Beach!5e1!3m2!1sen!2sth!4v1642869952544!5m2!1sen!2sth" width="100%" height="325px" frameborder="0" style="border:0; border-radius: 23px;" allowfullscreen=""></iframe>
                </div>
              </div>
              <div class="col-lg-12">
                <form id="contact-form" action="" method="post" data-contact-form>
                  <div class="row">
                    <div class="col-lg-6">
                      <fieldset>
                        <input type="name" name="name" id="name" placeholder="Your Name..." autocomplete="on" required>
                      </fieldset>
                    </div>
                    <div class="col-lg-6">
                      <fieldset>
                        <input type="surname" name="surname" id="surname" placeholder="Your Surname..." autocomplete="on" required>
                      </fieldset>
                    </div>
                    <div class="col-lg-6">
                      <fieldset>
                        <input type="text" name="email" id="email" pattern="[^ @]*@[^ @]*" placeholder="Your E-mail..." required="">
                      </fieldset>
                    </div>
                    <div class="col-lg-6">
                      <fieldset>
                        <input type="subject" name="subject" id="subject" placeholder="Subject..." autocomplete="on" >
                      </fieldset>
                    </div>
                    <div class="col-lg-12">
                      <fieldset>
                        <textarea name="message" id="message" placeholder="Your Message"></textarea>
                      </fieldset>
                    </div>
                    <div class="col-lg-12">
                      <fieldset>
                        <button type="submit" id="form-submit" class="orange-button">Send Message Now</button>
                      </fieldset>
                    </div>
                    <div class="col-lg-12">
                      <p class="small text-muted mt-2 mb-0" data-contact-status aria-live="polite"></p>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>  

  <div class="platform-subscribe" id="subscribe-now">
    <div class="container">
          <div class="row align-items-center gy-4">
        <div class="col-lg-7">
          <h4>Subscribe now for weekly platform updates</h4>
          <p>Get payout updates, new casino launches, and member-only sign-in tips delivered every week.</p>
          <div class="d-flex flex-wrap gap-2">
            <span class="badge-soft"><i class="fa fa-database" aria-hidden="true"></i>Data-powered insights</span>
            <span class="badge-soft"><i class="fa fa-envelope" aria-hidden="true"></i>Curated newsletters</span>
          </div>
        </div>
        <div class="col-lg-5">
          <form class="subscribe-form" data-subscribe-form>
            <div class="input-group">
              <input type="email" class="form-control" placeholder="Enter your email" aria-label="Email address" required>
              <button class="btn btn-accent" type="submit">Subscribe Now</button>
            </div>
          </form>
          <p class="small text-muted mt-2 mb-0" data-subscribe-status aria-live="polite"></p>
        </div>
      </div>
    </div>
  </div>

<?php include __DIR__ . '/partials/footer.php'; ?>
