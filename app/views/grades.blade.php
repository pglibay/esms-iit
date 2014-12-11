<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<style type="text/css">
		table tbody tr,
		table thead tr {
			height: 15;
			vertical-align: middle;
		}
		#grades tbody tr td,
		#grades thead tr th {
			border: 1px dotted #000;
		}
		#grades thead tr th,
		td.grades-under {
			border-bottom: 1px solid #000;
		}
		td.grades-meta, 
		td.grades-date {
			font-weight: bold;
		}
		.al {text-align: left;}
		.ar {text-align: right;}
		.ac {text-align: center;}
	</style>
</head>
<body>
	<table>
		<tbody>
			<tr><td colspan="8" class="ac">Bohol Island State University</td></tr>
			<tr><td colspan="8" class="ac">Main Campus</td></tr>
			<tr><td colspan="8" class="ac">Tagbilaran City, Bohol</td></tr>
			<tr><td colspan="8" class="ac">&nbsp;</td></tr>
			<tr><td colspan="8" class="ac">GRADING SHEET</td></tr>
			<tr><td colspan="8" class="ac">Sem: {{$data['meta']['sem']}} - SY: {{$data['meta']['sy']}}</td></tr>
		</tbody>
	</table>
	<table>
		<tbody>
			<tr>
				<td>&nbsp;</td>
				<td class="ar">Subject:</td>
				<td class="grades-meta" colspan="6">{{$data['meta']['subjcode']}} - {{$data['meta']['subjdesc']}}</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td class="ar">Instructor:</td>
				<td class="grades-under">&nbsp;</td>
				<td>&nbsp;</td>
				<td class="ar">Course:</td>
				<td class="grades-meta">{{$data['meta']['course']}}</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td class="ar">Adviser:</td>
				<td class="grades-under">&nbsp;</td>
				<td>&nbsp;</td>
				<td class="ar">Yr. & Sec.:</td>
				<td class="grades-meta">{{$data['meta']['section']}}</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
		</tbody>
	</table>
	@if ($data)
		<table id="grades">
			<thead>
				<tr>
					<th>#</th>
					<th>ID No.</th>
					<th>Full Name</th>
					<th class="ac">MT Grade</th>
					<th class="ac">FT Grade</th>
					<th class="ac">Final</th>
					<th class="ac">Re-Exam</th>
					<th>Remarks</th>
				</tr>
			</thead>
			<tbody>
				@foreach($data['data'] as $k => $v)
				<tr>
					<td class="al">{{$k + 1}}</td>
					<td class="al">{{$v['studid']}}</td>
					<td class="al">{{$v['fullname']}}</td>
					<td class="ac">{{$v['prelim1']}}</td>
					<td class="ac">{{$v['prelim2']}}</td>
					<td class="ac">{{$v['grade']}}</td>
					<td class="ac">{{$v['gcompl']}}</td>
					<td class="al">{{$v['remarks']}}</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	@endif
	<table>
		<tbody>
			<tr>
				<td colspan="2" class="grades-datelabel ar"><i>Date Printed:</i></td>
				<td class="grades-date"><i>{{$data['meta']['date']}}</i></td>
				<td>&nbsp;</td>
				<td class="al">Mid Term:</td>
				<td>&nbsp;</td>
				<td class="al">Final Term:</td>
				<td>&nbsp;</td>
			</tr>
			<tr><td colspan="8">&nbsp;</td></tr>
			<tr>
				<td class="ar" colspan="2">Submitted by:</td>
				<td class="grades-under">&nbsp;</td>
				<td>&nbsp;</td>
				<td class="grades-under">&nbsp;</td>
				<td>&nbsp;</td>
				<td class="grades-under">&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr><td colspan="8">&nbsp;</td></tr>
			<tr>
				<td class="ar" colspan="2">Approved by:</td>
				<td class="grades-under">&nbsp;</td>
				<td>&nbsp;</td>
				<td class="grades-under">&nbsp;</td>
				<td>&nbsp;</td>
				<td class="grades-under">&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
		</tbody>
	</table>
</body>
</html>