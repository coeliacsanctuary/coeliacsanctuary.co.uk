<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
</script>

<template>
  <div class="mt-5 flex flex-col gap-5">
    <Card class="mx-auto prose max-w-6xl">
      <Heading :border="false">Cookie Policy</Heading>

      <ol class="mt-0 space-y-2 text-sm">
        <li>
          To give our visitors the best experience and to ensure our website
          works properly we place small data files called cookies onto your
          device.
        </li>

        <li>
          <span class="font-semibold text-primary-dark">What Are Cookies</span>
          <ol>
            <li>
              A cookie is a small text file that a website places onto your
              device when you visit a website. It enabled a website to remember
              your actions, such as whats in your basket in the Coeliac
              Sanctuary shop. We also use cookies to store other little bits of
              information, such as whether you've seen our Cookie bar.
            </li>
          </ol>
        </li>

        <li>
          <span class="font-semibold text-primary-dark"
            >How do you use cookies?</span
          >
          <ol>
            <li>
              We use cookies in a couple of ways, both to protect your privacy,
              and to enhance the website performance.
            </li>
            <li>
              <span class="font-semibold text-primary-dark"
                >Protecting your privacy</span
              >
              <ol>
                <li>
                  We use cookies to make sure that your personal information
                  (such as when checking out on our shop) is only visible to
                  you, we do this by storing a token that is unique to your
                  device and expires when you leave our website.
                </li>
                <li>
                  We require these cookies to ensure that the website works
                  properly, without them we wont be able to store your data
                  securely between pages (Such as whats in your basket).
                </li>
              </ol>
            </li>
            <li>
              <span class="font-semibold text-primary-dark"
                >Website Performance</span
              >
              <ol>
                <li>
                  We also use cookies to ensure that our website runs smoothly
                  and efficiently, these are all anonymous and do not contain
                  any personal information.
                </li>
                <li>
                  These cookies are also mandatory as parts of our website will
                  not work correctly without them, and your experience will be
                  poor.}
                </li>
              </ol>
            </li>
          </ol>
        </li>
        <li>
          <span class="font-semibold text-primary-dark"
            >Third-party Cookies</span
          >
          <ol>
            <li>
              In addition to our own cookies, other parties beside Coeliac
              Sanctuary may also place their own anonymous cookies on your
              device, these could include Google, Twitter, Facebook, PayPal,
              Stripe and more.
            </li>
          </ol>
        </li>
        <li>
          <span class="font-semibold text-primary-dark"
            >How to control Cookies</span
          >
          <ol>
            <li>
              You can control and/or delete cookies as you wish, for more
              details see
              <a
                title="aboutcookies.org"
                href="http://www.aboutcookies.org"
                target="_blank"
                >aboutcookies.org</a
              >, you can easily delete all cookies that already stored on your
              device and update your browser settings to prevent new cookies
              being created. However, if you do this your experience on our
              website and others may be affected.
            </li>
          </ol>
        </li>
      </ol>
    </Card>
  </div>
</template>
