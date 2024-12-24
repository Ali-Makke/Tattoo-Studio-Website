<a href="backtotop:" id="return-to-top"><i></i></a>

<style>
    #return-to-top {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: rgba(0, 0, 0, 0.85);
        width: 50px;
        height: 50px;
        display: block;
        text-decoration: none;
        border-radius: 35px;
        display: none;
    }

    #return-to-top:hover {
        background: rgba(0, 0, 0, 0.9);
    }

    #return-to-top:hover i {
        color: #fff;
        top: 5px;
    }
</style>

<script>
    window.onscroll = function () {
  const scrollToTopBtn = document.getElementById('return-to-top');
  if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
    scrollToTopBtn.style.display = 'block';
  } else {
    scrollToTopBtn.style.display = 'none';
  }
};

document.getElementById('return-to-top').addEventListener('click', function (event) {
  event.preventDefault();
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
});
</script>