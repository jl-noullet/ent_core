// var canvas = document.getElementById("myCanvas");
// var ctx = canvas.getContext("2d");

// trouver la solution d'un système de 2 equations de la forme  
//	e[0]*x + e[1]*y = e[2]
function LP_cramer2( e1, e2 ) {
var det = e1[0] * e2[1] - e1[1] * e2[0];
if	( det == 0.0 )
	return [ 0, 0 ];
var sol = new Array();
sol[0] = ( e1[2] * e2[1] - e1[1] * e2[2] ) / det;
sol[1] = ( e1[0] * e2[2] - e1[2] * e2[0] ) / det;
return sol;
}

// trouver le peak du secteur, avec un offset off 
function LP_peak( ang0, ang1, off ) {
var cos0 = Math.cos( ang0 );
var sin0 = Math.sin( ang0 );
var cos1 = Math.cos( ang1 );
var sin1 = Math.sin( ang1 );
// les deux droites du secteur avant translation
// de la forme d[0]*x + d[1]*y = d[2]
var d0 = [ sin0, -cos0, 0 ];
var d1 = [ sin1, -cos1, 0 ];
// vecteurs de translation des 2 droites
x0 = off * -sin0; y0 = off *  cos0;	// off @ ang0 + pi/2 "vers l'interieur" du secteur
x1 = off *  sin1; y1 = off * -cos1;	// off @ ang1 - pi/2
// translation
// d0[2] = d0[0]* x0 + d0[1]* y0 + d0[2];	// <-- formule avant simplification
   d0[2] = sin0 * x0 - cos0 * y0;	// console.log('tran ' + x0 + ',' + y0 + ' -> ' + d0  );
   d1[2] = sin1 * x1 - cos1 * y1;	// console.log('tran ' + x1 + ',' + y1 + ' -> ' + d1  );
return LP_cramer2( d0, d1 );
}

function makestipple( ctx, quant ) {
var mycan = new Array();
var mypat = new Array();
var myctx;
for	( var i = 0; i < quant; i++ )
	{
	// on cree un canvas off-screen pour chaque stipple
	mycan[i] = document.createElement('canvas');
	// N.B. width et height sont des attributs HTML qui sont implicitement en pixels
	// et determinent les dimensions internes du canvas (indispensable)
	// style vient au-dessus mais n'a d'effet que si le canvas est visible
	mycan[i].setAttribute('style', 'width: 12px; height: 12px');
	mycan[i].setAttribute('width', 12 );
	mycan[i].setAttribute('height', 12 );
	myctx = mycan[i].getContext("2d");
	// myctx.fillStyle="#FFDD00";
	// myctx.fillRect(0,0,12,12);
	myctx.lineWidth = 1;
	myctx.strokeStyle = '#000';
	myctx.beginPath();
	switch	( i % 4 )
		{
		case 0:	// les ronds
			myctx.arc( 3, 3, 2.5, 0, Math.PI * 2 ); myctx.stroke(); myctx.beginPath();
			myctx.arc( 9, 9, 2.5, 0, Math.PI * 2 ); myctx.stroke();
		break;
		case 1:	// les vagues
			myctx.arc( 9, 3, 2.5, 0, Math.PI );
			myctx.arc( 3, 3, 2.5, 0, Math.PI, true ); myctx.stroke(); myctx.beginPath();
			myctx.arc( 9, 9, 2.5, 0, Math.PI );
			myctx.arc( 3, 9, 2.5, 0, Math.PI, true ); myctx.stroke();
		break;
		case 2:	// les hachures obliques
			myctx.moveTo( 0, 12 ); myctx.lineTo( 12, 0 );
			myctx.moveTo( 0, 6 ); myctx.lineTo( 6, 0 );
			myctx.moveTo( 6, 12 ); myctx.lineTo( 12, 6 ); myctx.stroke();
		break;
		case 3:	// les plus
			myctx.moveTo( 3, 0 ); myctx.lineTo( 3, 6 );
			myctx.moveTo( 0, 3 ); myctx.lineTo( 6, 3 );
			myctx.moveTo( 9, 6 ); myctx.lineTo( 9, 12 );
			myctx.moveTo( 6, 9 ); myctx.lineTo( 12, 9 ); myctx.stroke();
		break;
		}
	// pour debug seulement : afficher les petits canvas
	// document.body.appendChild(mycan[i]);
	mypat[i] = ctx.createPattern( mycan[i], "repeat" );
	}
return mypat;
}

function LP_pie( ctx, h, vals, colors, labels ) {
// normaliser les valeurs d'angles, en radian
var i, tot = 0;
for	( i in vals )
	tot += vals[i];
var k = 2 * Math.PI / tot;
for	( i in vals )
	vals[i] *= k;
// preparer layout
if	( colors == false )
	colors = makestipple( ctx, vals.length )
var offset = 2;
var radius = h/2 - offset;
var da = ( offset / radius );
var a0 = -0.5 * Math.PI;	// mettre origine en haut
var a1, pic;
var pat = new Array();
// tracer les parts de tarte, en commençant par les meilleurs 
ctx.save();
ctx.translate( h/2, h/2 );
for	( i = vals.length - 1; i >=0; i-- )
	{
	if	( vals[i] > 0.0 )
		{
		a1 = a0 + vals[i];
		ctx.beginPath();
		ctx.arc( 0, 0, radius, a0+da, a1-da );
		pic = LP_peak( a0, a1, offset )
		ctx.lineTo( pic[0], pic[1] );
		ctx.closePath();
		ctx.fillStyle = colors[i];
		ctx.fill();
		ctx.stroke();	
		a0 = a1;
		}
	}
ctx.restore();
// tracer la legende, les meilleurs en haut
ctx.font = "14px Arial";
var dy = Math.round( h / ( ( vals.length * 2 ) + 1 ) );	// intervalle pour legende
k = 100.0 / (2.0 * Math.PI);
var percent;
ctx.translate( dy + h, dy );
for	( i = vals.length - 1; i >=0; i-- )
	{
	ctx.fillStyle = colors[i];
	ctx.fillRect(0,0,dy,dy);
	ctx.strokeRect(0,0,dy,dy);
	ctx.fillStyle = "#000";
	percent = k * vals[i];
	percent = percent.toFixed(1) + '% ' + labels[i];
//	ctx.fillText( percent.toFixed(1).padStart(4, ' ') + '% ' + labels[i], dy+10, dy );
// 	padStart not supported by wkhtmltopdf !!!
	ctx.fillText( percent, dy+10, dy );
	ctx.translate( 0, dy*2 );
	}
}

// histogramme de notes en barres, une barre pour chaque intervalle [N N+1[
// levels[N] = index dans colors[]
// en notation sur 20, vals[] et levels[] ont 21 elements
function LP_histo_notes( ctx, w, h, vals, levels, colors ) {
const hfoot = 18;
var qnotes = vals.length;
var dx = w / qnotes;
var h0 = h - hfoot;	// h0 est la position du "pied" du graphe
var vmax = 0;
for	( i in vals )
	if	( vals[i] > vmax )
		vmax = vals[i];
var dy = h0 / vmax;
ctx.translate( 0, 2 );	// petite marge en haut
// tracer les lignes horizontales
ctx.lineWidth = 1;
ctx.strokeStyle = '#AAA';
var y = h0 - dy;
ctx.beginPath();
for	( i = 0; i < vmax; ++i )
	{
	ctx.moveTo( 0, y ); ctx.lineTo( w, y );
	y -= dy;
	}
ctx.stroke();
// tracer les barres
ctx.fillStyle = '#ddd';
ctx.strokeStyle = '#666';
var hbar, x = 1;
for	( i in vals )
	{
	ctx.fillStyle = colors[levels[i]];
	hbar = vals[i] * dy;
	ctx.fillRect( x, h0 - hbar , dx-3 , hbar );
	ctx.strokeRect( x, h0 - hbar , dx-3 , hbar );
	x += dx;
	}
// tracer l'echelle horizontale
ctx.lineWidth = 2;
ctx.strokeStyle = '#000';
ctx.beginPath();
ctx.moveTo( 0, h0 ); ctx.lineTo( w, h0 ); ctx.stroke();
ctx.font = "14px Arial";
ctx.fillStyle = '#000';
ctx.lineWidth = 1;
ctx.beginPath();
for	( i in vals )
	{
	ctx.moveTo( i * dx, h0 ); ctx.lineTo( i * dx, h0+4 );
	ctx.fillText( i, i * dx, h0 + hfoot - 2 );
	}
ctx.stroke();
}

// jeu de N barres simples
// names, vals, colors sont des arrays de N elements
// tick est l'intervalle de l'echelle verticale, dans la meme unite que vals
// valmax determine la hauteur graduee qui êut depasser le max effectif des data
// unit est un suffuxe optionnel t.q. ' %'
function LP_N_bars( ctx, w, h, names, vals, colors, tick, valmax, unit ) {
const mbot = 22;	// marge bottotm
const mtop = 20;	// marge top
const mleft = 40;	// marge left
var qbars = vals.length;
var dx = ( w - mleft ) / qbars;
var h0 = h - mbot - mtop;	// h0 est la position du "pied" du graphe
var ky = h0 / valmax;		// echelle verticale
var dy = tick * ky;		// intervalle graduations verticales
ctx.translate( 0, mtop );		// petite marge en haut
// tracer les labels et les lignes horizontales de l'echelle verticale
ctx.lineWidth = 1;
ctx.strokeStyle = '#AAA';
ctx.font = "12px Arial";
ctx.fillStyle = '#000';
var y = h0 - dy;
var p = tick;
ctx.beginPath();
while	( y >= 0 )
	{
	ctx.moveTo( mleft - 3, y ); ctx.lineTo( w, y );
	ctx.fillText( p+unit, 3, y + 1 );
	y -= dy; p += tick;
	}
ctx.stroke();

// tracer les barres et leurs labels
ctx.fillStyle = '#ddd';
ctx.strokeStyle = '#666';
ctx.font = "16px Arial";
ctx.fillStyle = '#000';
var hbar, x = mleft + 1;
for	( i in vals )
	{
	hbar = vals[i] * ky;
	ctx.fillStyle = colors[i];
	ctx.fillRect( x, h0 - hbar , dx-3 , hbar );
	ctx.strokeRect( x, h0 - hbar , dx-3 , hbar );
	ctx.fillStyle = '#000';
	ctx.font = "16px Arial";
	ctx.fillText( names[i], x + 3, h0 + mbot - 4 );
	ctx.font = "14px Arial";
	ctx.fillText( vals[i]+unit, x + 3, h0 - hbar - 4 );
	x += dx;
	}
// tracer les axes principaux
ctx.lineWidth = 2;
ctx.strokeStyle = '#000';
ctx.beginPath();
ctx.moveTo( 0, h0 ); ctx.lineTo( w, h0 );
ctx.moveTo( mleft, -mtop ); ctx.lineTo( mleft, h );
ctx.stroke();

}


/* petit HTML pour les test du canvas
<!DOCTYPE html>
<script src="./LP_func.js"></script>

<canvas id="myCanvas1" width="600" height="200" style="border: 1px solid #c3c3c3;"></canvas>
<hr>
<canvas id="myCanvas2" width="600" height="200" style="border: 1px solid #c3c3c3;"></canvas>
<hr>
<canvas id="myCanvas3" width="600" height="160" style="border: 1px solid #c3c3c3;"></canvas>

<script>
var canvas = document.getElementById("myCanvas1");
var ctx = canvas.getContext("2d");
LP_pie( ctx, 200, [ 7, 4, 2, 1 ], [ '#F44', '#FB0', '#0E0', '#08F' ], ['bad', 'no good', 'good', 'super'] );

canvas = document.getElementById("myCanvas2");
ctx = canvas.getContext("2d");
LP_pie( ctx, 200, [ 7, 4, 2, 1 ], false, ['bad', 'no good', 'good', 'super'] );

canvas = document.getElementById("myCanvas3");
ctx = canvas.getContext("2d");
LP_histo_notes( ctx, 600, 160, [ 1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 20, 18, 16, 14, 12, 10, 8, 6, 4, 2, 0 ],
[ 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 2, 2, 3, 3, 3, 3, 3, 3 ],
[ '#F44', '#FB0', '#0E0', '#08F' ] );

</script>
*/


