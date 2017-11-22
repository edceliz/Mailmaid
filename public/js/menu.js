/**
 * Opens navigation bar
 */
function openNav() {
    document.getElementById('nav').style.marginLeft = '0';
    document.getElementById('nav').style.padding = '10px';
    document.getElementById('navButton').style.marginLeft = '-100px';
    document.getElementById('body').style.marginLeft = '270px';
}

/**
 * Closes navigation bar
 */
function closeNav() {
    document.getElementById('nav').style.marginLeft = '-250px';
    document.getElementById('nav').style.padding = '10px 0';
    document.getElementById('navButton').style.marginLeft = '10px';
    document.getElementById('body').style.marginLeft = '0';
}
