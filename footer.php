<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        footer{
    background: var(--bark);
    padding: 88px 96px 48px;
}
.footer-grid{
    display: grid;
    grid-template-columns: 1.4fr 1fr 1fr 1fr;   
    gap: 4rem;
    margin-bottom: 3.5rem;
}
.footer-brand-name{
    font-family: 'Cormorant Garamond',serif;
    font-size: 35px;
    color: var(--blush);
    display: block;
    margin-bottom: 20px;
}
.footer-brand p{
    font-size: .82rem;
    color: var(--stone);
    line-height: 1.87;
    font-weight: 300;
    max-width: 240px;
    margin-bottom: 1.5rem;
}
.socials{
    display: flex;
    gap: .6rem;
}
.soc-link{
    width: 36px;
    height: 36px;
    border: 1px solid var(--dark);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--blush);
    text-decoration: none;
    cursor: pointer;
    font-weight: 500;
    font-size: 13px;
}
.soc-link:hover{
    border-color: var(--rose);
    color: var(--rose);
    background:rgba(252,143,143,.08);
}
.footer-col h4{
    font-size: 9.92px;
    letter-spacing: .24em;
    text-transform: uppercase;
    color: var(--stone);
    margin-bottom: 1.5rem;
}
.footer-col a{
    display: block;
    font-size: .82rem;
    color: var(--stone);
    text-decoration: none;
    margin-bottom: .72rem;
    transition: color .2s;
}
.footer-col a:hover{
    color:var(--blush);
}
.footer-bottom{
    border-top: 1px soild #DFD9D8;
    padding-top: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;  
}
.footer-bottom p{
    font-size: .7rem;
    color: var(--sand);
}
.contact-info{
    font-size: .78rem;
    color: var(--sand);
    line-height: 1.95;
}
.contact-info a{
    color: var(--blush);
    text-decoration: none;
}
.contact-info a:hover{
    color: var(--rose);
}
    </style>
</head>
<body>
    <footer>
    <div class="footer-grid">
        <div class="footer-brand">
            <span class="footer-brand-name">Sweet Haven</span>
            <p>Handcrafted home decor from the workshop of Nepal - designed for spaces that hold meanings.</p>
            <div class="socials">
                <a class="soc-link" href="#">IG</a>
                <a class="soc-link" href="#">FB</a>
                <a class="soc-link" href="#">YT</a>
                <a class="soc-link" href="#">PT</a>
            </div>
        </div>

        <div class="footer-col">
            <h4>Shop</h4>
            <a href="#">Wallpapers</a>
            <a href="#">Decor Items</a>
            <a href="#">Collections</a>
            <a href="#">New Arrivals</a>
        </div>
        <div class="footer-col">
            <h4>Explore</h4>
            <a href="#">Design DNA</a>
            <a href="#">Palette Builder</a>
            <a href="#">Our Story</a>
            <a href="#">Decor Journal</a>
        </div>
        <div class="footer-col">
            <h4>Help</h4>
            <a href="#">Contact Us</a>
            <a href="#">My Account</a>
            <a href="#">Admin</a>

        <div class="contact-info" style="margin-top: 1.5rem;">
            <div>Lalitpur,Nepal</div>
        <div><a href="#">hello@sweethaven.com</a></div>
        <div><a href="#">+977 9837491728</a></div>
    </div>
    </div>
    </div>
    
    <div class="footer-bottom">
        <p>© 2026 Sweet Haven Home Decor. All rights reserved.</p>
        <p>Made with care in Nepal.</p>
    </div>
</footer>
</body>
</html>