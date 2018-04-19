#
# LockScreen
# https://github.com/ducknorris/jquery-lockscreen
#
# Copyright (c) 2014 Catalin Ilinca
# Licensed under the MIT license.
#
# ------------------------------------------------
#
# This plugin is depending on jQuery Cookie Plugin
# jQuery Cookie Plugin v1.4.1
# https://github.com/carhartl/jquery-cookie
#
# Copyright 2013 Klaus Hartl
# Released under the MIT license
#
# ------------------------------------------------
#
# Default HTML Outpout Template
#
# <div class="lock-screen" style="display: none">
#   <div class="current-user">
#     <h2><img alt="__APP__" src="__LOGO__"></h2>
#     <span><img alt="__APP__" height="80" src="__AVATAR__" width="80"></span>
#     <h4>__NAME__</h4>
#     <input autofocus="" class="password-field" placeholder="__PLACEHOLDER__" type="password">
#     <button class="unlock-me">__UNLOCK__</button>
#   </div>
# </div>

(($)->

  lockScreen =
    init: (el, config) ->
      @.el = el
      @.$el = $(el)

      @.config = $.extend {}, $.fn.lockScreen.defaults, config

      @.$el.data 'original-title', window.document.title
      _originalTitle   = @.$el.data('original-title')
      window._timeoutInterval = @.config.timeout
      window._lockScreenFn    = @lockScreen
      window._locked          = @.config.locked

      @updateHtml()
      window._lockScreen    = $('.lock-screen')
      window._passwordField = $('.lock-screen .current-user .password-field')
      window._unlockMe      = $('.lock-screen .current-user .unlock-me')

      @lockScreen() if $.cookie('locked') == 'yes'

      @logout() if $('.lock-screen').length == 0

      window._timeout = setTimeout ->
        window._lockScreenFn()
        return
      , window._timeoutInterval

      @guard()
      return

    updateHtml: ->
      # CSS Class
      @.$el.addClass "lockableScreen"

      # HTML Template
      @.$el.prepend @.config.template

      # HTML Template Placeholders
      # App Logo
      @.$logo = $('.lock-screen .current-user h2 img')
      @.$logo.attr 'alt', @.config.app
      @.$logo.attr 'src', @.config.logo
      # Current User
      @.$avatar = $('.lock-screen .current-user span img')
      @.$avatar.attr 'alt', @.config.name
      @.$avatar.attr 'src', @.config.avatar
      @.$current_user = $('.lock-screen .current-user h4')
      @.$current_user.html @.config.name
      # Password field
      @.$passwordField = $('.lock-screen .current-user .password-field')
      @.$passwordField.attr 'placeholder', @.config.placeholder
      # Unlock button
      @.$unlockMe = $('.lock-screen .current-user .unlock-me')
      @.$unlockMe.addClass @.config.unlockBtnClass
      @.$unlockMe.html @.config.unlockMe
      return

    lockScreen: ->
      window.document.title = window._locked
      window._passwordField.focus()
      window._lockScreen.fadeIn()
      $.cookie('locked', 'yes')
      return

    guard: ->
      _this = @
      $(document).on 'mousemove', () ->
        _this.config.logout() if $('.lock-screen').length == 0
        clearTimeout window._timeout
        if window._lockScreen.is(':hidden')
          window._timeout = setTimeout ->
            window._lockScreenFn()
            return
          , window._timeoutInterval
        return

  $.fn.lockScreen = (config)->
    obj = Object.create(lockScreen)
    @each ->
      obj.init this, config
      $(document).bind 'keydown', (evt) ->
        evt.preventDefault() if evt.keyCode == 116
        return
      $('.unlock-me').bind 'click', () ->
        config.unlock() if typeof config.unlock == 'function'
        return
      return

  $.fn.lockScreen.defaults =
    timeout: 60000
    logout: ->
      console.warn 'You have to implement this function!'
      return
    unlock: ->
      console.warn 'You have to implement this function!'
      return
    app: 'jQuery LockScreen'
    locked: 'Locked!'
    logo: 'http://jquery-lockscreen.s3.amazonaws.com/logo.png'
    bg: 'http://jquery-lockscreen.s3.amazonaws.com/lock-screen.jpg'
    name: 'John Doe'
    avatar: 'http://jquery-lockscreen.s3.amazonaws.com/current-user.png'
    placeholder: 'type password'
    unlockMe: "I'm here. Let me in!"
    unlockBtnClass: ""
    template: '<div class="lock-screen" style="display: none">
        <div class="current-user">
          <h2><img alt="" src="" class="logo"></h2>
          <span><img alt="" height="80" src="" width="80"></span>
          <h4></h4>
          <input autofocus="" class="password-field" placeholder="" type="password">
          <button class="unlock-me"></button>
        </div>
      </div>'

)(jQuery)
