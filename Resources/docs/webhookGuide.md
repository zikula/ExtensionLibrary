Quick Guide to Github WebHook set up
====================================

###Step 1
Navigate to your project repo and click on `Settings`

![repo menu][img1]

###Step 2
Click on `Webhooks & Services`

![settings menu][img2]

###Step 3
Click `Add webhook`

![webhooks menu][img3]

###Step 4
Fill out the form exactly as shown below, and click `Add webhook`

![add webhook form][img4]

###Step 5 - Finished!
When finished it should look like this:

![webhook list][img5]

###Step 6 - Confirmation
After pushing a Tag, make sure everything worked by clicking on the webhook once again and scroll down. You should see
something like this:

![webhook recent deliveries][img6]

Click on the three little dots at the right hand side and then on the `Response` tab. It worked correctly if it says
`Ping event received` at the bottom. If it did not work correctly, the response will indicate what errors need to be
corrected before another attempt.

![webhook response][img7]
