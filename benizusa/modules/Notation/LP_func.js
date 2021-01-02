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
		case 0:	// les plus
			myctx.moveTo( 3, 0 ); myctx.lineTo( 3, 6 );
			myctx.moveTo( 0, 3 ); myctx.lineTo( 6, 3 );
			myctx.moveTo( 9, 6 ); myctx.lineTo( 9, 12 );
			myctx.moveTo( 6, 9 ); myctx.lineTo( 12, 9 ); myctx.stroke();
		break;
		case 1:	// les hachures obliques
			myctx.moveTo( 0, 12 ); myctx.lineTo( 12, 0 );
			myctx.moveTo( 0, 6 ); myctx.lineTo( 6, 0 );
			myctx.moveTo( 6, 12 ); myctx.lineTo( 12, 6 ); myctx.stroke();
		break;
		case 2:	// les vagues
			myctx.arc( 9, 3, 2.5, 0, Math.PI );
			myctx.arc( 3, 3, 2.5, 0, Math.PI, true ); myctx.stroke(); myctx.beginPath();
			myctx.arc( 9, 9, 2.5, 0, Math.PI );
			myctx.arc( 3, 9, 2.5, 0, Math.PI, true ); myctx.stroke();
		break;
		case 3:	// les ronds
			myctx.arc( 3, 3, 2.5, 0, Math.PI * 2 ); myctx.stroke(); myctx.beginPath();
			myctx.arc( 9, 9, 2.5, 0, Math.PI * 2 ); myctx.stroke();
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
var tot = 0;
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
// tracer les parts de tarte
ctx.save();
ctx.translate( h/2, h/2 );
for	( i in vals )
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
// tracer la legende
ctx.font = "14px Arial";
var dy = Math.round( h / ( ( vals.length * 2 ) + 1 ) );	// intervalle pour legende
k = 100.0 / (2.0 * Math.PI);
var percent;
ctx.translate( dy + h, dy );
for	( i in vals )
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

/* petit HTML pour les test du canvas
<!DOCTYPE html>
<script src="./LP_func.js"></script>
<canvas id="myCanvas" width="600" height="200" style="border: 1px solid #c3c3c3;" >

<script>
var canvas = document.getElementById("myCanvas");
var ctx = canvas.getContext("2d");
// LP_pie( ctx, 200, [ 1, 2, 4, 7 ], ['#08F', '#0E0', '#FB0', '#F44'], ['super', 'good', 'not good', 'bad'] );
LP_pie( ctx, 200, [ 1, 2, 4, 7 ], false, ['super', 'good', 'not good', 'bad'] );
</script>
*/
