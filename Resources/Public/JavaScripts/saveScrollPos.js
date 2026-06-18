/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*****************************************!*\
  !*** ./Assets/Scripts/saveScrollPos.js ***!
  \*****************************************/

  document.addEventListener('DOMContentLoaded', function () {
    // Function to scroll to the hash
    function scrollToHash () {
      const hash = window.location.hash
      if (hash) {
        const element = document.querySelector(hash)
        if (element) {
          element.scrollIntoView({behavior: 'smooth'})
        }
      }
    }

    const timeoutDuration = 600
    setTimeout(scrollToHash, timeoutDuration)
  })
document.addEventListener('DOMContentLoaded', function () {
  const setScrollPosition = localStorage.getItem('setScrollPosition')
  if (setScrollPosition) {
    const globalScrollPosition = localStorage.getItem('scrollPosition')
    window.scrollTo({
      top: globalScrollPosition,
      left: 0,
      behavior: 'instant'
    })
    localStorage.setItem('setScrollPosition', false)
    localStorage.setItem('scrollPosition', 0)
  }

  function setupScrollPositionStorage (classSelector) {
    const scrollToButtons = document.querySelectorAll(classSelector)
    Array.prototype.forEach.call(scrollToButtons, function (scrollButton) {
      scrollButton.addEventListener('click', function () {
        const globalScrollPosition = window.scrollY
        localStorage.setItem('scrollPosition', globalScrollPosition.toString())
        localStorage.setItem('setScrollPosition', true)
      })
    })
  }
  setupScrollPositionStorage('.saveScrollPosition')
})

/******/ })()
;
//# sourceMappingURL=saveScrollPos.js.map
