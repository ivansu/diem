<?php

echo £('div.s16.s16_info', __('This chart is not available.'));

if ($sf_user->can('google_analytics'))
{
  echo £link('@dm_google_analytics')->text('To enable it, provide a google analytics access');
}