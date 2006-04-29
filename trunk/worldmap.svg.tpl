<?xml version="1.0" standalone="no"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.0//EN" 
   "http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd">

<svg onload="init(evt)" zoomAndPan="disable"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">

<title>promed map</title>

<defs>
<!-- JAVASCRIPT -->

<!-- WEEKLY DATA -->
</defs>

    <g>
        <rect height="410" width="1020" stroke="black" y="0" x="0" id="mainMapBG" fill="white"/>
        <rect height="20" width="1020" stroke="black" y="410" x="0" id="mainTxtBG" fill="#4682B4" opacity="1"/>
        <text font-size="12" y="424" x="50" font-family="Bitstream Vera Sans" id="txStatus" fill="white">Roll mouse over country to get country info</text>
    	<text font-size="12" y="424" x="500" font-family="Bitstream Vera Sans" id="txReport" fill="yellow">status</text>
	<text font-size="12" y="400" x="20" font-family="Bitstream Vera Sans" id="txWeekSpan" fill="black" >""</text>
     </g>

     <g id="Maplegend" >
	<rect height="15" width="30" stroke="black" y="310" x="30" fill="#A52A2A"/>
	<rect height="15" width="30" stroke="black" y="325" x="30" fill="#4682B4"/>	
	<rect height="15" width="30" stroke="black" y="340" x="30" fill="#B0E0E6"/>
	<text font-size="10" y="322" x="65" font-family="Bitstream Vera Sans" fill="black" >&gt;= 8 reports</text>
	<text font-size="10" y="337" x="65" font-family="Bitstream Vera Sans" fill="black" >4 - 7 reports</text>	
	<text font-size="10" y="352" x="65" font-family="Bitstream Vera Sans" fill="black" >1 - 3 reports</text>
     </g>

<!-- MAP DATA -->

<!-- X and Year is dynamic for axes title -->
    <g id="axes">
        <rect id="rectgraph1" height="200" width="680" stroke="black" y="435" x="0" fill="white" />
        <rect id="rectgraph3" height="200" width="330" stroke="black" y="435" x="690" fill="white" />
        <line id="wk_x_axis" shape-rendering="crispEdges" x1="12" y1="600" x2="660" y2="600" style="stroke:#778899;stroke-width:1"/>
        <line id="wk_y_axis" shape-rendering="crispEdges" x1="12" y1="600" x2="12" y2="480" style="stroke:#778899;stroke-width:1"/>
        <text id="titleWks" x="300" y="450" font-family="Bitstream Vera Sans" style="font-weight:normal;text-anchor:middle;font-size:12"> Number of ProMED reports by week (2006) </text>
        <text id="titleWks" x="590" y="620" font-family="Bitstream Vera Sans"  style="font-weight:normal;text-anchor:middle;font-size:10"> Calendar week numbers </text>
    </g>

    <g>
<a xlink:href="" >
<!-- GRAPH RECTANGLES -->
</a>
<!-- X AXIS LABELS -->

    </g>

</svg>
