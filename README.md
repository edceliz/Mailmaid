# Mailmaid
It is an <strong>E-mail Marketing Website Application</strong> that lets website owners build their e-mail contact list and implement marketing.

<h2>Features:</h2>
<ol>
  <li>Get insights on your audience growth, custom link interactions and mailing performance.</li>
  <li>Convert your links into trackable ones to know how many is interacting with it.</li>
  <li>Execute, track and manage campaigns with TinyMCE powered text editor with automatic image upload support.</li>
  <li>Get your business growing with this free, simple and easy to use application!</li>
</ol>

<h2>Requirements:</h2>
<ol>
  <li>PHP 5.X</li>
  <li>MySQL 5.X</li>
  <li>Access to Cron</li>
  <li>Access to E-mail Resources</li>
</ol>

<h2>Installation:</h2>
<ol>
  <li>
    Upload the project into your website server's root.
    <br>
    <em>Sample: <strong>/public_html/mailmaid</strong></em>
  </li>
  <li>Create a blank database and (optional) different account with atleast <em>CREATE, SELECT, UPDATE, DELETE and INSERT</em> permission.</li>
  <li>
    Create a cron job that runs every 5 minutes with a command to execute <em><strong>path_to_mailmaid/app/Mailer.php</strong></em>
    <br>
    <em>Sample: <strong>php -q /home/{cpanel_username}/public_html/mailmaid/app/Mailer.php >/dev/null</strong></em>
  </li>
  <li>
    Visit Mailmaid
    <br>
    <em>Sample: <strong>www.yourwebsite.com/mailmaid</strong></em>
  </li>
  <li>Fill up the setup form appropriate details</li>
  <li>Login using your newly registered account from setup! Congratulations!</li>
</ol>

<h2>Adding Subscribers</h2>
<p>Mailmaid doesn't come with its own script to subscribe users easily but it provides an end-point to subscribe users. Mailmaid doesn't currently support a way to insert contacts from outside sources.</p>
<pre>POST /path_to_mailmaid/subscriber/subscribe</pre>
<p>Fields to send: email, listId</p>
<p><strong>email</strong> - E-mail address of the subscriber.</p>
<p><strong>listId</strong> - ID of the subscriber list. You can find this when going to Mailmaid's panel->Subscribers</p>
<pre>Response: { status: true|false }</pre>

<h2>Mails Per Hour (Settings)</h2>
<p>Mailmaid is not responsible for any misuse of server's resources. There is a setting in the admin panel to provide the allotted e-mail that can be sent per hour. Be sure to know your server provider what is the allotted e-mails that can be sent per hour by your account. It is heavily recommended to avoid using the whole allocation in order to provide resources to other applications that might use it.</p>

<h2>Message:</h2>
<p>This is an application developed with complete documentation for my thesis in Introduction to Software Engineering during 1st trimester of S.Y. 2017-2018 in AMA Computer College Las Piñas. If you want to get a copy, send an inuiry or report an issue, you can contact me at <a href='mailto:edceliz01@gmail.com'>edceliz01@gmail.com</a>.</p>
