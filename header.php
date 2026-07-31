<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>
        :root{
 --blush:#FFDBBB;
 --sand: #CCBEB1; 
 --clay: #997E67;
 --bark: #664930;
 --wine: #64242F;
 --coral: #B44446;
  --rose:#FC8F8F;
  --stone: #DFD9D8;
  --dark: #2C1A0E;
  --white: #FDFAF7;
}
*, *::before, *::after{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
html{
    scroll-behavior: smooth;
}
body{
    font-family: 'Jost', sans-serif;
    background: var(--cream);
    color: var(--dark);
    overflow-x: hidden;
}

/* HEADER */
header{
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 999;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 3rem; height: 74px;
    background:rgba(255,219,187,0.92);
    border-bottom: 1px solid 1px solid rgba(100,36,47,0.14);
    transition: background 0.3s, box-shadow .35s
}
header.scrolled{
    background: rgba(255,219,187,0.99);
    box-shadow: 0 2px 28px rgba(100,36,47,0.13);
}

/* LOGO */
.logo{
    display: flex;
    align-items: center;
    gap: 0.7rem;
    text-decoration: none;
    flex-shrink: 0;
}
.logo img{
    height: 60px; width: 60px;
    object-fit: contain;
}
.logo-text{
    font-family:Cormorant Garamond;
    font-size: 1.45rem;
    font-weight: 600;
    color: var(--wine);
    letter-spacing: 0.04rem;
}

nav{
    display: flex;
    gap: .1rem;
}
nav a{
    font-size: 0.77rem;
    
    letter-spacing: 0.05rem;
    text-transform: uppercase;
    font-weight: 500;
    color: var(--bark); text-decoration: none;
    padding: 0.45rem .9rem;
    border-radius: 2rem;
    transition: background 0.2s, color 0.2s;
}
nav a:hover,nav a.active{
    background: var(--wine);
    color: var(--blush);
    display: inline-block;
    border-radius: 100px;
    padding: 0.4rem 1.3rem;
}
.header-right{
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.search-box{
    display: flex;
    align-items: center;
    background: rgba(100,36,47,0.09);
    border: 1px solid rgba(100, 36, 47, 0.14);
    border-radius: 2rem;
    padding: 0.35rem 1rem; gap: 0.5rem; 
}
.search-box input{
    border: none;
    background: transparent;
    font-family: 'Jost',sans-serif;
    font-size: 0.82rem;
    color: var(--dark);
    outline: none;
    width: 120px;
}
.search-box input::placeholder{
    color:var(--clay);
}
.icon-btn{
    background: none;
    border: none;
    cursor: pointer;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center; justify-content: center;
    color: var(--wine);
    transition: background 0.2s;
    position: relative;
}
.icon-btn:hover{
    background: rgba(100,36,47,0.1);
}
.cart-badge{
    position: absolute;
    top: 2px; right: 2px;
    background:var(--wine);
    color: #fff;
    font-size: 0.6rem;
    width: 16px; height: 16px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

    </style>
</head>
<body>
    
<div class="tst" id="tst"></div>
    <header id="header">
        <a href="#" class="logo">
            <img src="sweet-haven-logo-2.png" alt="Sweet Haven Logo">
            <span class="logo-text">Sweet Haven</span>
        </a>
    <nav>
        <a href="#" class="active">Home</a>
        <a href="wallpaper.php">Wallpaper</a>
        <a href="#shop">Shop</a>
        <a href="#decor">Decor</a>
        <a href="#collections">Collections</a>
        <a href="#about">About</a>
    </nav>
    <div class="header-right">
        <div class="search-box">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#997E67" stroke-width="2">
                <circle cx="11" cy="11" r="8"><path d="M21 21l-4.35-4.35"/> </circle>
            </svg>
    <input type="text" name="" id="" placeholder="Search decor...">
    </div>

    <button class="icon-btn" title="Account">
        <i class="fa-thin fa-circle-user" style="color: rgb(99, 230, 190);"></i>
    </button>

    <button class="icon-btn" title="cart" style="position: relative;">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      <span class="cart-badge" id="cartCount">0</span>
    </button>

    
</div>
</header>
</body>
</html>