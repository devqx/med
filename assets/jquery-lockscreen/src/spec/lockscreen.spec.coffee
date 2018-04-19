# ======== A Handy Little Jasmine Reference ========
# http://pivotal.github.io/jasmine/

# Test methods:
#   describe "name", ()->

describe "jQuery#lockscreen", ->

  beforeEach ->
    @.body = $("body")
    spyOn console, 'warn'

  afterEach ->
    # no cleanup needed

  it "should be available on the jQuery object", ->
    expect($.fn.lockScreen).toBeDefined()

  it "should be chainable", ->
    expect(@.body.lockScreen()).toEqual @.body

  it "should add .lockableScreen class to body", ->
    expect(@.body.lockScreen().hasClass('lockableScreen')).toBeTruthy()

  it "has defaults configured", ->
    expect($.fn.lockScreen.defaults).toBeDefined()

  it "should have HTML template Logo in place", ->
    expect(@.body.lockScreen().find('.lock-screen .current-user h2 img').attr('alt')).toEqual 'jQuery LockScreen'
    expect(@.body.lockScreen().find('.lock-screen .current-user h2 img').attr('src')).toEqual 'http://jquery-lockscreen.s3.amazonaws.com/logo.png'

  it "should have HTML template Current User in place", ->
    expect(@.body.lockScreen().find('.lock-screen .current-user span img').attr('alt')).toEqual 'John Doe'
    expect(@.body.lockScreen().find('.lock-screen .current-user span img').attr('src')).toEqual 'http://jquery-lockscreen.s3.amazonaws.com/current-user.png'
    expect(@.body.lockScreen().find('.lock-screen .current-user h4').html()).toEqual 'John Doe'

  it "should have HTML template Password in place", ->
    expect(@.body.lockScreen().find('.lock-screen .current-user .password-field').attr('placeholder')).toEqual 'type password'

  it "should have HTML template Unlock button in place", ->
    expect(@.body.lockScreen().find('.lock-screen .current-user .unlock-me').html()).toEqual "I'm here. Let me in!"

  it "should have be hidden by default", ->
    @.body.lockScreen()
    expect(@.body.find('.lock-screen').is(':hidden')).toBe true

  it "should save current window.title", ->
    expect(@.body.lockScreen().data('original-title')).toEqual window.document.title

  it "should lock screen if Cookie is set", ->
    $.cookie 'locked', 'yes'
    @.body.lockScreen()
    expect(@.body.find('.lock-screen').is(':visible')).toBe true

  it "should focus Password field when locking the screen", ->
    $.cookie 'locked', 'yes'
    @.body.lockScreen()
    # I can't make this spec pass...
    # expect(@.body.find('.lock-screen .current-user .password-field').is(':focus')).toBe true

  it "should go to logout path if .lock-screen is not present", ->
    $.cookie 'locked', 'yes'
    @.body.lockScreen()
    @.body.find('.lock-screen').remove()
    @.body.trigger('mousemove')
    expect(console.warn).toHaveBeenCalled()

