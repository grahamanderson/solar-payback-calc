#!/ncdc/solaris/bin/perl5.004_04 


use DBI;

require "/opt/oracle/app/oracle/product/7.3.3/ows/3.0/sample/perl/mycgi-lib.pl";
require "/WWW/csdapps/cdo/climatenormals_dev_cfg.pl";

&ReadParse;

#----------------------------------------------------------------------------------------
# Read in cgi vars....
#----------------------------------------------------------------------------------------

$directive=$in{directive};
$state=$in{state};
$station=$in{station};
$ename=$in{ename};
$begyear=$in{begyear};
$begmonth=$in{begmonth};
$begday=$in{begday};
$endyear=$in{endyear};
$endmonth=$in{endmonth};
$endday=$in{endday};
$evyear=$in{evyear};
$evmonth=$in{evmonth};
$evday=$in{evday};
$evhour=$in{evhour};
$evmin=$in{evmin};
$evname=$in{evname};
$location=$in{location};
$satname=$in{satname};
$channel=$in{channel};
$enh=$in{enh};
$media=$in{media};
$proj=$in{proj};
$mapping=$in{mapping};
$spatres=$in{spatres};
$source=$in{source};
$imres=$in{imres};
$purchase=$in{purchase};
$avail=$in{avail};
$comments=$in{comments};
$filename=$in{filename};
$fname=$in{fname};
$path=$in{path};
$size=$in{size};
$from=$in{from};
$recnum=$in{recnum};
$mostpop=$in{mostpop};
$pop=$in{pop};
$welpool=$in{welpool};
$tinypath=$in{tinypath};
$tinyfname=$in{tinyfname};
$delete=$in{delete};
$hresfname=$in{hresfname};
$new=$in{new};
$type=$in{type};
$subrnum=$in{subrnum};
$prodtype=$in{prodtype};

#-------------------------------------------------------------------------------------------------------
# Fill $stime variable with system time - used in web page time stamps...
#-------------------------------------------------------------------------------------------------------

$stime=`date`;

#-------------------------------------------------------------------------------------------------------
# Get user domain and ip address......
#-------------------------------------------------------------------------------------------------------

$udomain = $ENV{REMOTE_HOST};
$ipaddress = $ENV{REMOTE_ADDR};



#-------------------------------------------------------------------------------------------------------
# Get accesstype and chargetype information
#-------------------------------------------------------------------------------------------------------

if(defined($dbh)) { $rc=$dbh->disconnect; }
oracle_connect_charge();
get_chargetype();
if(defined($dbh)) { $rc=$dbh->disconnect; }

do_oracle_connect();

if ($subrnum ne '') {
   
   check_subscription();

   if ($validsub eq 'NO' or $subexp eq 'YES') {

      $directive='subscription_error';
      $subrnum='';

   }

}

#----------------------------------------------------------------------------------------
# Begin Web Page
#----------------------------------------------------------------------------------------

print &PrintHeader;

if ($directive eq '') {
   
   $directive = 'prod_select';

}
   
   #update climaps_stats table
   do_oracle_connect();
   update_climaps_stats();
   if(defined($dbh)) { $rc=$dbh->disconnect; }

   do $directive();
   climatenormals_web_footer();
   if(defined($dbh)) { $rc=$dbh->disconnect; }


#-------------------------------------------------------------------------------------------------------
# prod_select2
#-------------------------------------------------------------------------------------------------------

sub prod_select2 {

   climatenormals_web_header('prod_select2','prodseltxt.jpg','','Climate Normals Product Selection');

   print '<CENTER>';

   print <<OUTPUT;
<table border=0 cellpadding=4>
<TR>
<TD colspan=2 align=center valign=top>
OUTPUT
   
if ($prodtype eq 'CLIM84') {

   form_tag('prod_select3');
   print <<OUTPUT;
<Font size=-1><B>Daily Station Normals - CLIM84</B> (available by station)</font><BR>
<input type=hidden name=prodtype value="$prodtype">
OUTPUT

} elsif ($prodtype eq 'CLIM81') {

   form_tag('prod_select3');
   print <<OUTPUT;
<Font size=-1><B>Monthly Station Normals - CLIM81</B> (available by state)</font><BR>
<input type=hidden name=prodtype value="$prodtype">
OUTPUT

} elsif ($prodtype eq 'CLIM20') {

   form_tag('prod_select3');
   print <<OUTPUT;
<Font size=-1><B>Monthly Station Climate Summaries - CLIM20</B> (available by station)</font><BR>
<input type=hidden name=prodtype value="$prodtype">
OUTPUT


} elsif ($prodtype eq 'CLIM8101') {

   print <<OUTPUT;
<Font size=-1><B>Monthly Precipitation Probabilities - CLIM81 Supplement Number 1</B></font><BR>
OUTPUT

} elsif ($prodtype eq 'CLIM8102') {

   print <<OUTPUT;
<Font size=-1><B>Annual Degree Days to Selected Bases - CLIM81 Supplement Number 2</B></font><BR>
OUTPUT

} elsif ($prodtype eq 'CLIM2002') {

   print <<OUTPUT;
<Font size=-1><B>National Weather Service Snow Normals 1971-2000 - CLIM20-02</B></font><BR>
OUTPUT

} elsif ($prodtype eq 'CLIM85') {

   print <<OUTPUT;
<Font size=-1><B>Monthly Divisional Normals & Standard Deviations - CLIM85</B></font><BR>
OUTPUT

} elsif ($prodtype eq 'HCS5') {

   print <<OUTPUT;
<Font size=-1><B>Population-Weighted State, Regional, and National Monthly Degree Days <BR>1971-2000 (and
previous normals periods)</B> (HCS 5-1;2)</B></font><BR>
OUTPUT

} elsif ($prodtype eq 'HCS4') {

   print <<OUTPUT;
<Font size=-1><B>Area-Weighted State, Regional, and National Monthly and Seasonal Temp. and Precip.<BR>1971-2000 (and
previous normals periods) (HCS 4-1;2;3)</B></font><BR>
OUTPUT

}

print <<OUTPUT;
</TD>
</TR>
<TR>
<TD align=left valign=top>
OUTPUT

if ($prodtype eq 'CLIM84') {

#<CENTER><A HREF="http://www5.ncdc.noaa.gov/climaps2/newclim84sample.pdf" target="_viewwindow"><IMG SRC="http://www5.ncdc.noaa.gov/climaps2/newclim84sample.jpg"></A>
#<BR><A HREF="http://www5.ncdc.noaa.gov/climaps2/newclim84sample.pdf" target="_viewwindow"> <font size=-1>Sample Publication</font></A><BR>
#<BR>
#</center>

   print <<OUTPUT;
<font size=-1>This product includes daily 1971-2000 normal maximum, minimum, and mean temperature
(degrees F), heating and cooling degree days (base 65 degrees F), and precipitation (inches)
for selected cooperative and First-Order stations. Monthly, seasonal, and annual normals of
these elements are also presented. Monthly and annual precipitation probabilities and quintiles
are also included.  The data are published by station.
<A HREF="http://www.ncdc.noaa.gov/oa/climate/normals/usnormalsprods.html#CLIM84" target="_viewwindow">See complete details for this product.</A>
  1 year of <a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=subscription&subrnum=$subrnum">unlimited access</A> to this product can also be purchased at a reduced cost. </font>
</TD>
<TD align=center valign=top>
<BR><Font size=-1 color=red><B>Select a state:</B></font><BR>
OUTPUT

state_pop('','11','Y','');

   print <<OUTPUT;
<BR><BR>
<input type=hidden name=subrnum value=$subrnum>
<input type=submit value="Continue" title='Continue Button'>
</form>
OUTPUT

} elsif ($prodtype eq 'CLIM81') {

   print <<OUTPUT;
<CENTER><A HREF="http://www5.ncdc.noaa.gov/climaps2/newclim81sample.pdf" target="_viewwindow"><IMG SRC="http://www5.ncdc.noaa.gov/climaps2/newclim81sample.jpg"></A>
<BR><A HREF="http://www5.ncdc.noaa.gov/climaps2/newclim81sample.pdf" target="_viewwindow"><font size=-1>Sample Publication</font></A><BR>
<BR>
</center>
<font size=-1>This product includes normals of average monthly and annual maximum, minimum, and mean
temperature (degrees F), monthly and annual total precipitation (inches), and heating and
cooling degree days (base 65 degrees F) for individual locations for the 1971-2000 period.

<A HREF="http://www.ncdc.noaa.gov/oa/climate/normals/usnormalsprods.html#CLIM81" target="_viewwindow">See complete details for this product.</A>
  1 year of <a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=subscription&subrnum=$subrnum">unlimited access</A> to this product can also be purchased at a reduced cost.  This product can also
  be purchased on a <A HREF="http://nndc.noaa.gov/?http://ols.nndc.noaa.gov/plolstore/plsql/olstore.prodspecific?prodnum=C00115-CDR-S0002">CD-ROM</A>.</font>
</TD>
<TD align=center valign=top>
<BR><Font size=-1 color=red><B>Select a state:</B></font><BR>
OUTPUT

state_pop('','11','Y','');

   print <<OUTPUT;
<BR><BR>
<input type=hidden name=subrnum value=$subrnum>
<input type=submit value="Continue" title='Continue Button'>
</form>
OUTPUT

} elsif ($prodtype eq 'CLIM20') {

   print <<OUTPUT;
<CENTER><A HREF="http://www5.ncdc.noaa.gov/climaps2/clim20sample.pdf" target="_viewwindow"><IMG SRC="http://www5.ncdc.noaa.gov/climaps2/clim20sample1.jpg"></A>
<BR><A HREF="http://www5.ncdc.noaa.gov/climaps2/clim20sample.pdf" target="_viewwindow"><font size=-1>Sample Publication</font></A><BR>
<BR>
</center>
<font size=-1>The Climatography of the United States No. 20 Monthly Station Climate Summaries for the 1971-2000 period of record are station summaries of particular interest to agriculture, industry and
engineering applications and include a variety of statistics for temperature, precipitation, snow, and degree day elements for 4,273 stations throughout the conterminous United States (stations
in Alaska, Hawaii, Puerto Rico, Virgin Islands, and Pacific Territories will be added late spring 2004) . The new CLIM20's update and expand on the previous version (1,879 stations) which was
last published in 1985 and covered the period 1951-1980. The types of statistics include means, median (precipitation and snow elements), extremes, mean number of days exceeding threshold
values, and probabilities for monthly precipitation and freeze data. There is also a table for each station with heating, cooling, and growing degree days for various temperature bases.

<A HREF="http://www.ncdc.noaa.gov/oa/documentlibrary/pdf/eis/clim20eis.pdf" target="_viewwindow">See complete details for this product.</A>
  1 year of <a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=subscription&subrnum=$subrnum">unlimited access</A> to this product can also be purchased at a reduced cost.</font>
</TD>
<TD align=center valign=top>
<BR><Font size=-1 color=red><B>Select a state:</B></font><BR>
OUTPUT

state_pop('','11','Y','');

   print <<OUTPUT;
<BR><BR>
<input type=hidden name=subrnum value=$subrnum>
<input type=submit value="Continue" title='Continue Button'>
</form>
OUTPUT


} elsif ($prodtype eq 'CLIM8101') {

   print <<OUTPUT;
<font size=-1>This publication presents the monthly and annual precipitation values 
(in inches) corresponding to three probability levels: 0.10, 0.50, and 0.90.
 The stations are listed alphabetically.  The probability tables in this product 
 are determined by fitting the 1971-2000 historical monthly precipitation 
 to a Gamma distribution (Crutcher et al., 1977; Crutcher and Joiner, 1978).  
 The process was performed with the historical data for each of the twelve months and 
 separately with the annual values to produce 13 sets of probability values for each 
 station.  <A HREF="http://www.ncdc.noaa.gov/oa/climate/normals/usnormalsprods.html#CLIM81-01" target="_viewwindow">See complete details for this product.</A>
 <BR><BR>Click on the links provided below to access this publication in either Adobe PDF
 or ASCII format.
 <BR><BR>
 <IMG ALT="Very Small Image with the word FREE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/free.jpg"><A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim81_supp/CLIM81_Sup_01.pdf"><B>Monthly Precipitation Probabilities in Adobe PDF format.</A><BR><BR>
 <IMG ALT="Very Small Image with the word FREE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/free.jpg"><A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim81_supp/CLIM81_Sup_01.dat"><B>Monthly Precipitation Probabilities in ASCII format.<A>
 <BR>   <BR>
 <IMG ALT="Very Small Image with the words ONLINE STORE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/onlinestoreicon\$.gif"><a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=olstore&subrnum=$subrnum&prodtype=$prodtype">
 Purchase a hardcopy version of this publication.</a>
 </font>
OUTPUT

} elsif ($prodtype eq 'CLIM8102') {

   print <<OUTPUT;
<font size=-1>This product presents annual heating degree day normals to the following 
bases (in degrees F): 65, 60, 57, 55, 50, 45, and 40, and annual cooling degree day 
normals to the following bases (also in degrees F): 70, 65, 60, 57, 55, 50, and 45. 
The values were computed for all Climatography of the United States No. 81 temperature 
stations and are summarized alphabetically by state within each state or territory.   
<A HREF="http://www.ncdc.noaa.gov/oa/climate/normals/usnormalsprods.html#CLIM81-02" target="_viewwindow">See complete details for this product.</A>
<BR><BR>Click on the links provided below to access this publication in either Adobe PDF
or ASCII format.
<BR><BR>
<IMG ALT="Very Small Image with the word FREE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/free.jpg"><A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim81_supp/CLIM81_Sup_02.pdf"><B>Annual Degree Days to Selected Bases (CLIM81-02) in Adobe PDF format.</A><BR><BR>
<IMG ALT="Very Small Image with the word FREE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/free.jpg"><A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim81_supp/CLIM81_Sup_02.dat"><B>Annual Degree Days to Selected Bases (CLIM81-02) in ASCII format.<A>
<BR>   <BR>
<IMG ALT="Very Small Image with the words ONLINE STORE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/onlinestoreicon\$.gif"><a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=olstore&subrnum=$subrnum&prodtype=$prodtype">
Purchase a hardcopy version of this publication.</a>
</font>

OUTPUT

} elsif ($prodtype eq 'CLIM2002') {

   print <<OUTPUT;
<font size=-1>A climate normal is defined, by convention, as the arithmetic mean of a 
climatological element computed over three consecutive decades (WMO, 1989).  
Based upon this definition, snow normals have been computed for the 1971-2000 
period for over 500 National Weather Service first-order stations.  
Please see <A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim20-02/normalsnwssnow.pdf">attached PDF file</A> for more detailed information on this product, including a station inventory and file format for ASCII files.
    Snow normals were computed for mean snowfall (503 stations), mean snow depth (267 stations), 
number of days with snowfall >=0.1" (525 stations), and number of days with snowfall >=1.0" 
(525 stations).  The snow normals data includes four monthly ASCII files, two daily ASCII files (derived from the monthly products), and three easy-to-read formatted files.  
<BR><BR>
<B>ASCII Data Files:</B>
<BR>
<IMG ALT="Very Small Image with the word FREE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/free.jpg"><A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim20-02/NWS_SNOW_MNFALL_mth.dat"><B>Mean snowfall (monthly).</b></A><BR>
<IMG ALT="Very Small Image with the word FREE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/free.jpg"><A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim20-02/NWS_SNOW_MNDPTH_mth.dat"><B>Mean snow depth (monthly).</b></A><BR>
<IMG ALT="Very Small Image with the word FREE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/free.jpg"><A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim20-02/NWS_SNOW_NDYF01_mth.dat"><B>Number of days with snowfall >=0.1" (monthly).</b></A><BR>
<IMG ALT="Very Small Image with the word FREE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/free.jpg"><A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim20-02/NWS_SNOW_NDYF10_mth.dat"><B>Number of days with snowfall >=1.0" (monthly).</b></A><BR>
<IMG ALT="Very Small Image with the word FREE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/free.jpg"><A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim20-02/NWS_SNOW_MNFALL_dly.dat"><B>Mean snowfall (daily).</b></A><BR>
<IMG ALT="Very Small Image with the word FREE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/free.jpg"><A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim20-02/NWS_SNOW_MNDPTH_dly.dat"><B>Mean snow depth (daily).</b></A><BR>
<BR><BR>
<B>Formatted Files:</B>
<BR>
<IMG ALT="Very Small Image with the word FREE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/free.jpg"><A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim20-02/NWS_SNOW_MNFALL_fmt.dat"><B>Mean snowfall (monthly/daily).</b></A><BR>
<IMG ALT="Very Small Image with the word FREE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/free.jpg"><A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim20-02/NWS_SNOW_MNDPTH_fmt.dat"><B>Mean snow depth (monthly/daily).</b></A><BR>
<IMG ALT="Very Small Image with the word FREE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/free.jpg"><A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim20-02/NWS_SNOW_NDYFXX_fmt.dat"><B>Number of days with snowfall >=0.1";1.0".</b></A><BR>
</font>

OUTPUT

} elsif ($prodtype eq 'CLIM85') {

   print <<OUTPUT;
<font size=-1>This product includes normals and standard deviations for the five 30-year 
periods and the 70-year period between 1931-2000 for each division in a state.  The normals and 
standard deviations include values for each of the 12 calendar months 
and an annual value.  The divisional data are displayed by name and number for a state 
or island. The states and islands include the contiguous United States, Alaska, Puerto 
Rico, and the Virgin Islands, and are arranged alphabetically.<A HREF="http://www.ncdc.noaa.gov/oa/climate/normals/usnormalsprods.html#CLIM85"
target="_viewwindow">See complete details for this product.</A>  The CLIM85 product is available in 6 seperate Adobe PDF
files.  In some cases, there is also an ASCII file version available.  The PDF and ASCII files are available for download free of charge.  You can also purchase a hardcopy version of any PDF file.
  Click on the links provided below to access the CLIM85 files, or purchase a hardcopy.
<BR><BR>
<B>Temperature Values - <A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim85/CLIM85_TEMP01.pdf">PDF</A> (2.3Mb)  -- <A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim85/CLIM85_TEMP01.dat">ASCII</a> (.5Mb) -- <IMG ALT="Very Small Image with the words ONLINE STORE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/onlinestoreicon\$.gif"><a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=olstore&subrnum=$subrnum&prodtype=$prodtype-003">Hardcopy</a><BR>
<B>Precipitation Values - <A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim85/CLIM85_PRCP02.pdf">PDF</A> (2.4Mb)  -- <A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim85/CLIM85_PRCP02.dat">ASCII</a> (.5Mb) -- <IMG ALT="Very Small Image with the words ONLINE STORE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/onlinestoreicon\$.gif"><a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=olstore&subrnum=$subrnum&prodtype=$prodtype-004">Hardcopy</a><BR>
<B>Heating Degree Day Values - <A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim85/CLIM85_HDD03.pdf">PDF</A> (2.4Mb) -- <A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim85/CLIM85_HDD03.dat">ASCII</a> (.5Mb) -- <IMG ALT="Very Small Image with the words ONLINE STORE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/onlinestoreicon\$.gif"><a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=olstore&subrnum=$subrnum&prodtype=$prodtype-005">Hardcopy</a><BR>
<B>Cooling Degree Day Values - <A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim85/CLIM85_CDD04.pdf">PDF</A> (2.3Mb) -- <A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim85/CLIM85_CDD04.dat">ASCII</a> (.5Mb) -- <IMG ALT="Very Small Image with the words ONLINE STORE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/onlinestoreicon\$.gif"><a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=olstore&subrnum=$subrnum&prodtype=$prodtype-006">Hardcopy</a><BR>
<B>Color Maps of 1971-2000 Divisional Normals - <A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim85/CLIM85_71MAP05.pdf">PDF</A> (9.6Mb) -- <IMG ALT="Very Small Image with the words ONLINE STORE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/onlinestoreicon\$.gif"><a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=olstore&subrnum=$subrnum&prodtype=$prodtype-007">Hardcopy</a><BR>
<B>Color Maps of 1931-2000 Long-Term Div. Means - <A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim85/CLIM85_31MAP06.pdf">PDF</A> (9.1Mb) -- <IMG ALT="Very Small Image with the words ONLINE STORE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/onlinestoreicon\$.gif"><a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=olstore&subrnum=$subrnum&prodtype=$prodtype-008">Hardcopy</a>
<BR><BR>
<IMG ALT="Very Small Image with the words ONLINE STORE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/onlinestoreicon\$.gif"><a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=olstore&subrnum=$subrnum&prodtype=$prodtype-009">
Purchase a hardcopy version of this entire publication (all items listed above).</a>
</font>
OUTPUT

} elsif ($prodtype eq 'HCS5') {

   print <<OUTPUT;
<font size=-1>The population weights for U.S. Climate Divisions are computed from the 2000 Census 
county and metropolitan populations in that division.  Divisional population totals are summed
from 2000 county totals for counties residing completely within a given division.  For 
counties residing in more than one division, 2000 county populations are divided proportionally 
by overlaying the climate divisions on a one-kilometer squared population database based on 
the 1990 census and provided by the Socioeconomic Data Application Center (SEDAC).  
Approximately 25%, or about 800 out of 3200 counties, require division in this manner.  
Once divisional totals are determined, their proportion in the context of the state, division, 
region, and nation are determined.<BR><BR>
Click on the links provided below to access this publication in either Adobe PDF
or ASCII format.
<BR><BR> 
<B>HCS 5-1:  State, Regional, and National Monthly
Heating Degree Days (Weighted by Population (2000 Census)), 1971-2000 (and
previous normals periods):
<BR><A HREF="http://www5.ncdc.noaa.gov/climatenormals/hcs/HCS_51.pdf">PDF</A> (1Mb)
<BR><A HREF="http://www5.ncdc.noaa.gov/climatenormals/hcs/HCS_51_seq.txt">ASCII (month-year sequential values)</A> (.4Mb)  
<BR><A HREF="http://www5.ncdc.noaa.gov/climatenormals/hcs/HCS_51_avg.txt">ASCII (means/totals/standard deviations)</A> (.1Mb)
<BR><BR>
<B>HCS 5-2:  State, Regional, and National Monthly
Cooling Degree Days (Weighted by Population (2000 Census)), 1971-2000 (and
previous normals periods):
<BR><A HREF="http://www5.ncdc.noaa.gov/climatenormals/hcs/HCS_52.pdf">PDF</A> (1Mb)
<BR><A HREF="http://www5.ncdc.noaa.gov/climatenormals/hcs/HCS_52_seq.txt">ASCII (month-year sequential values)</A> (.4Mb)  
<BR><A HREF="http://www5.ncdc.noaa.gov/climatenormals/hcs/HCS_52_avg.txt">ASCII (means/totals/standard deviations)</A> (.1Mb)
<BR><BR><B>HCS 4-1,2,3;5-1,2:  Area and 2000 Census
Population Weights by State:
<BR><A HREF="http://www5.ncdc.noaa.gov/climatenormals/hcs/HCS_WGT.txt">ASCII</A> (4,673Kb)
<BR><BR><B>HCS 4-1,2;5-1,2:  Color Maps of State Normals:
<BR><A HREF="http://www5.ncdc.noaa.gov/climatenormals/hcs/HCS_MAP_7100.pdf">PDF (1971-2000 State Normals) </A> (3.6Kb)
<BR><A HREF="http://www5.ncdc.noaa.gov/climatenormals/hcs/HCS_MAP_3100.pdf">PDF (1931-2000 State Normals) </A> (3.9Kb)
</font>
OUTPUT

} elsif ($prodtype eq 'HCS4') {

   print <<OUTPUT;
<font size=-1>Each month, averages of temperature and precipitation are calculated for U.S. Climate 
Divisions by simple averaging of data from all stations within the division that record both temperature 
and precipitation.  A division represents a region within a state that is climatically quasi-homogeneous 
or, in some cases, a semi-homogeneous dranage basin (as described by CLIM85). 
The average monthly temperature and precipitation for a state are derived from the divisional values by 
weighting each division by its percentage of the total state area, including the 48 contiguous states, 
Alaska, Hawaii, Puerto Rico, and the Virgin Islands.  The District of Columbia is treated as part of Maryland.
<BR><A HREF="http://www.ncdc.noaa.gov/oa/climate/normals/usnormalsprods.html#HCS4"
target="_viewwindow">See complete details for this product.</A>
<BR><BR>
Click on the links provided below to access this publication in either Adobe PDF
or ASCII format.
<BR><BR> 
<B>HCS 4-1:  State, Regional, and National Monthly
Temperature (Weighted by Area), 1971-2000 (and previous normals periods):
<BR><A HREF="http://www5.ncdc.noaa.gov/climatenormals/hcs/HCS_41.pdf">PDF</A> (1Mb)
<BR><A HREF="http://www5.ncdc.noaa.gov/climatenormals/hcs/HCS_41_seq.txt">ASCII (month-year sequential values)</A> (.5Mb)  
<BR><A HREF="http://www5.ncdc.noaa.gov/climatenormals/hcs/HCS_41_avg.txt">ASCII (means/totals/standard deviations)</A> (.1Mb)
<BR><BR>
<B>HCS 4-2:  State, Regional, and National Monthly
Precipitation (Weighted by Area), 1971-2000 (and previous normals periods):
<BR><A HREF="http://www5.ncdc.noaa.gov/climatenormals/hcs/HCS_42.pdf">PDF</A> (1Mb)
<BR><A HREF="http://www5.ncdc.noaa.gov/climatenormals/hcs/HCS_42_seq.txt">ASCII (month-year sequential values)</A> (.5Mb)  
<BR><A HREF="http://www5.ncdc.noaa.gov/climatenormals/hcs/HCS_42_avg.txt">ASCII (means/totals/standard deviations)</A> (.1Mb)
<BR><BR>
<B>HCS 4-3:  State, Regional, and National Seasonal
Temperature and Precipitation (Weighted by Area), 1971-2000 (and previous
normals periods):
<BR><A HREF="http://www5.ncdc.noaa.gov/climatenormals/hcs/HCS_43.pdf">PDF</A> (1Mb)
<BR><A HREF="http://www5.ncdc.noaa.gov/climatenormals/hcs/HCS_43_avg.txt">ASCII (means/totals/standard deviations)</A> (.1Mb)
<BR><BR><B>HCS 4-1,2,3;5-1,2:  Area and 2000 Census
Population Weights by State:
<BR><A HREF="http://www5.ncdc.noaa.gov/climatenormals/hcs/HCS_WGT.txt">ASCII</A> (4,673Kb)
<BR><BR><B>HCS 4-1,2;5-1,2:  Color Maps of State Normals:
<BR><A HREF="http://www5.ncdc.noaa.gov/climatenormals/hcs/HCS_MAP_7100.pdf">PDF (1971-2000 State Normals) </A> (3.6Kb)
<BR><A HREF="http://www5.ncdc.noaa.gov/climatenormals/hcs/HCS_MAP_3100.pdf">PDF (1931-2000 State Normals) </A> (3.9Kb)
</font>
OUTPUT

} elsif ($prodtype eq 'CLIM2001') {

   print <<OUTPUT;
   <TABLE cellPadding=4 border=0>
        <TBODY>
        <TR>
          <TD vAlign=top align=middle colSpan=2><FONT size=-1><B>FREEZE/FROST 
            DATA - CLIM20 supp no. 1</B> </FONT><BR><INPUT type=hidden 
            value=CLIM20sup1 name=prodtype> </TD></TR>
        <TR>
          <TD vAlign=top align=left colSpan=3><FONT size=-1>This product 
            contains station freeze/frost probability tables for each state. 
            Given are the dates of probable first and last occurrence, during 
            the year beginning August 1 and ending July 31 of freeze related 
            temperatures, probable duration where the temperature exceeds 
            certain freeze related values; and the probability of experiencing a 
            given temperature, or less, during the year period August 1 through 
            July 31. For the fall and spring dates of occurrence, and 
            freeze-free period, probabilities are given for three temperatures 
            (36, 32, and 28 °f) at three probability levels (10, 50, and 90 
            percent). <A href="file:///I:/normals/clim20/supp1/doc/TD9712C.doc" 
            target=_viewwindow>See complete details for this product.</A> 
            Options below are to select Freeze/Frost tables by state in PDF 
            format or all states in ASCII format. A complete list of stations 
            grouped by state is also available in PDF format.</FONT></TD></TR>
        <TR>
          <TD>Select a State(PDF) :</TD>
          <TD>All States(ASCII):</TD>
          <TD>Station List:</TD></TR>
        <TR>
          <TD>
            <FORM name=example><SELECT onchange=go() size=6 name=navi> <OPTION 
              value=start>Click State Name<OPTION 
              value=/climatenormals/clim20/supp1/states/AL.pdf>Alabama<OPTION 
              value=states/AK.pdf>Alaska<OPTION 
              value=states/AZ.pdf>Arizona<OPTION 
              value=states/AR.pdf>Arkansas<OPTION 
              value=states/CA.pdf>California<OPTION 
              value=states/CO.pdf>Colorado<OPTION 
              value=states/CT.pdf>Connecticut<OPTION 
              value=states/DE.pdf>Delaware<OPTION 
              value=states/FL.pdf>Florida<OPTION 
              value=states/GA.pdf>Georgia<OPTION 
              value=states/HI.pdf>Hawaii<OPTION value=states/ID.pdf>Idaho<OPTION 
              value=states/IL.pdf>Illinois<OPTION 
              value=states/IN.pdf>Indiana<OPTION value=states/IO.pdf>Iowa<OPTION 
              value=states/KS.pdf>Kansas<OPTION 
              value=states/KY.pdf>Kentucky<OPTION 
              value=states/LA.pdf>Louisiana<OPTION 
              value=states/ME.pdf>Maine<OPTION 
              value=states/MD.pdf>Maryland<OPTION 
              value=states/MA.pdf>Massachusetts<OPTION 
              value=states/MI.pdf>Michigan<OPTION 
              value=states/MN.pdf>Minnesota<OPTION 
              value=states/MS.pdf>Mississippi<OPTION 
              value=states/MO.pdf>Missouri<OPTION 
              value=states/MT.pdf>Montana<OPTION 
              value=states/NE.pdf>Nebraska<OPTION 
              value=states/NV.pdf>Nevada<OPTION value=states/NH.pdf>New 
              Hampshire<OPTION value=states/NJ.pdf>New Jersey<OPTION 
              value=states/NM.pdf>New Mexico<OPTION value=states/NY.pdf>New 
              York<OPTION value=states/NC.pdf>North Carolina<OPTION 
              value=states/ND.pdf>North Dakota<OPTION 
              value=states/OH.pdf>Ohio<OPTION 
              value=states/OK.pdf>Oklahoma<OPTION 
              value=states/OR.pdf>Oregon<OPTION 
              value=states/PA.pdf>Pennsylvania
            <OPTION value=states/RI.pdf>Rhode Island<OPTION 
              value=states/SC.pdf>South Carolina<OPTION 
              value=states/SD.pdf>South Dakota<OPTION 
              value=states/TN.pdf>Tennessee<OPTION 
              value=states/TX.pdf>Texas<OPTION value=states/UT.pdf>Utah<OPTION 
              value=states/VT.pdf>Vermont<OPTION 
              value=states/VA.pdf>Virginia<OPTION 
              value=states/WA.pdf>Washington<OPTION value=states/WV.pdf>West 
              Virginia<OPTION value=states/WI.pdf>Wisconsin<OPTION 
              value=states/WY.pdf>Wyoming</OPTION></SELECT> 
            <CENTER></CENTER></FORM></TD>
          <TD vAlign=top><A 
            href="file:///I:/normals/clim20/supp1/9712_frz_prob_1971-2000">Freeze/Frost 
            Data</A></TD>
          <TD vAlign=top><A 
            href="file:///I:/normals/clim20/supp1/doc/freeze-list.pdf">Freeze/Frost 
            List.pdf</A></TD></TR></TBODY></TABLE></CENTER>
OUTPUT

}

   print <<OUTPUT;
</CENTER>
</TD>
</TR>
</table>
</CENTER>
OUTPUT

      
   return;

}

#-------------------------------------------------------------------------------------------------------
# prod_select2
#-------------------------------------------------------------------------------------------------------

sub prod_select3 {

   if ($prodtype eq 'CLIM81') {
      order_details($prodtype,$state,$subrnum);
   } else {

      climatenormals_web_header('prod_select2','prodseltxt.jpg','','Climate Normals Product Selection');
   
      print '<CENTER>';

      if ($prodtype eq 'CLIM84') {

         print <<OUTPUT;
  <Font size=-1 color=red><B>Select a station below and then click 'Continue'</B></font>
  <BR><B><Font color=red size=-1>Note:</font></B> <Font size=-1>Stations below followed by a <B>(P)</B> are <B>precipitation only</B>.
  <BR>Other stations will have all available meteorological elements.
  <table border=0 cellpadding=4>
  <TR>
  <TD align=center valign=top>
OUTPUT

      } else {

         print <<OUTPUT;
<Font size=-1 color=red><B>Select a station below and then click 'Continue'</B></font>
<table border=0 cellpadding=4>
<TR>
<TD align=center valign=top>
OUTPUT

      }
   
         
   form_tag('order_details');

   station_pop('<Font size=-1>Stations:</font>','10','Y','');

   print <<OUTPUT;
<BR><BR>
<input type=hidden name=state value="$state">
<input type=hidden name=prodtype value="$prodtype">
<input type=hidden name=subrnum value=$subrnum>
<input type=submit value="Continue">
</form>
</CENTER>
</TD>
</TR>
</table>
</CENTER>
OUTPUT
      
      return;

   }


}


#-------------------------------------------------------------------------------------------------------
# olstore
#-------------------------------------------------------------------------------------------------------

sub olstore {

   climatenormals_web_header('olstore','N/A','');

   if ($prodtype eq 'CLIM20.stn') {

   print <<OUTPUT;
<META http-equiv="refresh" content="3; url=$olstoreurl?prod=C00113-PUB-S0002&subarr=004&quan=1&varname=PDF&varvalue=$filename" TARGET="_TOP">
OUTPUT

   }

   if ($prodtype eq 'CLIM20.state') {

   print <<OUTPUT;
<META http-equiv="refresh" content="3; url=$olstoreurl?prod=C00113-PUB-S0002&subarr=005&quan=1&varname=PDF&varvalue=$filename" TARGET="_TOP">
OUTPUT

   }

   
   if ($prodtype eq 'CLIM84') {
      
      print <<OUTPUT;
<META http-equiv="refresh" content="3; url=$olstoreurl?prod=C00117-PUB-A0001&subarr=001&quan=1&varname=PDF&varvalue=$filename" TARGET="_TOP">
OUTPUT

   }

   if ($prodtype eq 'CLIM81') {
      
      print <<OUTPUT;
<META http-equiv="refresh" content="3; url=$olstoreurl?prod=C00115-PUB-S0001&subarr=007&quan=1&varname=PDF&varvalue=$filename" TARGET="_TOP">
OUTPUT

   }

   if ($prodtype eq 'CLIM8101') {
      
      print <<OUTPUT;
<META http-equiv="refresh" content="3; url=$olstoreurl?prod=C00115-PUB-S0001&subarr=010&varname=&varvalue=&quan=1" TARGET="_TOP">
OUTPUT

   }

   if ($prodtype eq 'CLIM8102') {
      
      print <<OUTPUT;
<META http-equiv="refresh" content="3; url=$olstoreurl?prod=C00115-PUB-S0001&subarr=011&varname=&varvalue=&quan=1" TARGET="_TOP">
OUTPUT

   }

   if (substr($prodtype,0,6) eq 'CLIM85') {

      $prod  = substr($prodtype,0,6);
      $subset = substr($prodtype,7,3);

   print <<OUTPUT;
<META http-equiv="refresh" content="3; url=$olstoreurl?prod=C00118-PUB-S0001&subarr=$subset&varname=&varvalue=&quan=1" TARGET="_TOP">
OUTPUT

   }

   print <<OUTPUT;
<BR>
<BR>
<BR>
<CENTER>
<IMG SRC="http://www5.ncdc.noaa.gov/cdo/olstore.gif" border=0 alt="Entering NNDC Online Store, Please Wait...">
</CENTER>
<BR>
<BR>
<BR>
<BR>
OUTPUT

   return;

}


#-------------------------------------------------------------------------------------------------------
# order_details
#-------------------------------------------------------------------------------------------------------

sub order_details {

   if(defined($dbh)) { $rc=$dbh->disconnect; }
   
   do_oracle_connect();

   if ($prodtype eq 'CLIM84') {
      
      $ores = $dbh->prepare(qq{ select filename, station_name from climate_normals where filename='$station' and clim84='Y'});
      $ores->execute;
      $c_rec = $ores->bind_columns(undef, \$filename, \$stationname);

      while ($ores -> fetch) {}
      $ores->finish();

   } elsif ($prodtype eq 'CLIM20') {
      
      $ores = $dbh->prepare(qq{ select filename, station_name, LOWER(state_abb) from climate_normals where filename='$station' and clim20='Y'});
      $ores->execute;
      $c_rec = $ores->bind_columns(undef, \$filename, \$stationname, \$stateabb);

      while ($ores -> fetch) {}
      $ores->finish();

   } else {

      $ores = $dbh->prepare(qq{ select distinct state_abb||'norm' from climate_normals where state='$state' and clim81='Y'});
      $ores->execute;
      $c_rec = $ores->bind_columns(undef, \$filename);

      while ($ores -> fetch) {}
      $ores->finish();
      
   }
   
   if ($chargetype eq 'PAY') {

      $txtimage = 'ordinfo.jpg';
      $txtalt = 'Important Ordering Information';

   } else {

      $txtimage = 'accessnormstxt.jpg';
      $txtalt = 'Access/Download Normals';
   
   }

   climatenormals_web_header('order_details',$txtimage,$descript,$txtalt);
   
   print <<OUTPUT;
<Table width=550 border=0>
<TR>
<TD valign=top width=500>
<font size=-1>
OUTPUT
   
   if ($prodtype eq 'CLIM84') {
      #Adobe PDF files require the <A HREF="http://www.adobe.com/products/acrobat/readstep.html">Adobe Acrobat Reader</A> for viewing.  
      print <<OUTPUT;
<B>Daily station normals (1971-2000)</B> are provided in 2 formats, ASCII text and Web Form.
This product includes daily 1971-2000 normal maximum, minimum, and mean temperature (degrees F), 
heating and cooling degree days (base 65 degrees F), and precipitation (inches). Monthly, seasonal, 
and annual normals of these elements are also presented. 
Monthly and annual precipitation probabilities and quintiles are also included.<BR><BR>
<BR>
OUTPUT

   } elsif ($prodtype eq 'CLIM81') {

     print <<OUTPUT;
<B>Monthly station normals (1971-2000)</B> are provided in two formats, Adobe PDF and ASCII text.
This product includes normals of average monthly and annual maximum, minimum, 
and mean temperature (degrees F), monthly and annual total precipitation (inches), 
and heating and cooling degree days (base 65 degrees F) for individual locations 
for the 1971-2000 period. <BR><BR>
Adobe PDF files require the <A HREF="http://www.adobe.com/products/acrobat/readstep.html">Adobe Acrobat Reader</A> for viewing.  
<BR>
OUTPUT

   } elsif ($prodtype eq 'CLIM20') {

        print <<OUTPUT;
   <B>Monthly Station Climate Summaries</B> are provided in Adobe PDF format.
   This product includes means, median (precipitation and snow elements), extremes, mean number of days exceeding threshold values, and probabilities for monthly precipitation and freeze data.<BR><BR>
   Adobe PDF files require the <A HREF="http://www.adobe.com/products/acrobat/readstep.html">Adobe Acrobat Reader</A> for viewing.  
   <BR>
OUTPUT


   }
   
   print <<OUTPUT;
</TD>
</TR>
<TR>
<TD>
<BR>
OUTPUT

if ($chargetype eq 'FREE') {

   if ($prodtype eq 'CLIM84') {
   
      print <<OUTPUT;
<font size=-1><B>To download <font color=red>Daily Station Normals</font> for <font color=red>$stationname, $state</font>, click on the links provided below.</font><BR><BR>
OUTPUT

   } elsif ($prodtype eq 'CLIM81') {
           print <<OUTPUT;
<font size=-1><B>To download <font color=red>Monthly Station Normals</font> for <font color=red>$state</font>, click on the links provided below.</font><BR><BR>
OUTPUT

   } elsif ($prodtype eq 'CLIM20') {
              print <<OUTPUT;
<font size=-1><B>To download <font color=red>Monthly Station Climate Summaries</font> for <font color=red>$stationname, $state</font>, click on the links provided below.</font><BR><BR>
OUTPUT


   }

   if ($validsub eq 'YES') {

      $cpid=substr($filename,2,6);
      print '<font size=-1><B><Font color=RED>Note:</font> You will be asked to re-enter your subscription username/password in UPPERCASE.</font></B><BR>';
      $formurl='http://www.ncdc.noaa.gov/servlets/DNRM?coopid='.$cpid.'&random_number='.$subrnum;
   
   }  else {

      $cpid=substr($filename,2,6);
      $formurl='http://www.ncdc.noaa.gov/servlets/DNRM?coopid='.$cpid;
   }


   if ($prodtype eq 'CLIM84') {

      $statedir=substr($filename,0,2);
   
      #print <<OUTPUT;
#<font size=-1><A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim84/$statedir/$filename.pdf" target="_viewwindow">http://www5.ncdc.noaa.gov/climatenormals/clim84/$statedir/$filename.pdf</A> - PDF Format</font><BR><BR>
#OUTPUT
   
      print <<OUTPUT;
<font size=-1><A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim84/$statedir/$filename.txt" target="_viewwindow">http://www5.ncdc.noaa.gov/climatenormals/clim84/$statedir/$filename.txt</A> - ASCII Format</font><BR>
<BR>
<font size=-1><A HREF="$formurl" target="_viewwindow">$formurl</A> - Web Form</font>
<BR>                                                                                                      
OUTPUT
      
   }

   if ($prodtype eq 'CLIM81') {
   
      print <<OUTPUT;
<font size=-1><A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim81/$filename.pdf" target="_viewwindow">http://www5.ncdc.noaa.gov/climatenormals/clim81/$filename.pdf</A> - PDF Format</font><BR><BR>
OUTPUT
   
      print <<OUTPUT;
<font size=-1><A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim81/$filename.txt" target="_viewwindow">http://www5.ncdc.noaa.gov/climatenormals/clim81/$filename.txt</A> - ASCII Format</font><BR>
<BR>                                                                                                      
OUTPUT
      
   }

   if ($prodtype eq 'CLIM20') {
   
      print <<OUTPUT;
<font size=-1><A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim20/$stateabb/$filename.pdf" target="_viewwindow">http://www5.ncdc.noaa.gov/climatenormals/clim20/$stateabb/$filename.pdf</A> - PDF Format</font><BR><BR>
<BR>
<font size=-1><B>To download <font color=red>Monthly Station Climate Summaries</font> for all stations in <font color=red>$state</font>, click on the link provided below.</font><BR><BR>
<font size=-1><A HREF="http://www5.ncdc.noaa.gov/climatenormals/clim20/state-pdf/$stateabb.pdf" target="_viewwindow">http://www5.ncdc.noaa.gov/climatenormals/state-pdf/$stateabb.pdf</A> - PDF Format</font><BR><BR>
OUTPUT

           
   }


}

if ($chargetype eq 'PAY') {

   if ($prodtype eq 'CLIM84') {
   
      print <<OUTPUT;
<font size=-1><B>Click the 'Add To Shopping Cart' icon below to add <font color=red>Daily Station Normals</font> for <font color=red>$stationname, $state</font> to your shopping cart.  The price for this product is <Font color=red>\$1.00</font>, and the product will be
delivered online soon after purchase.  You will be provided with both a Web Form, and an ASCII file.</font><BR><BR>
OUTPUT

   } elsif ($prodtype eq 'CLIM81') {
           print <<OUTPUT;
<font size=-1><B>Click the 'Add To Shopping Cart' icon below to add <font color=red>Monthly Station Normals</font> for <font color=red>$state</font> to your shopping cart.  The price for this product is <Font color=red>\$3.00</font>, and the product will be
delivered online soon after purchase.  You will be provided with both an Adobe PDF file, and an ASCII file.</font><BR><BR>
OUTPUT

   } elsif ($prodtype eq 'CLIM20') {
              print <<OUTPUT;
   <font size=-1><B>Click the 'Add To Shopping Cart' icon below to add <font color=red>Monthly Station Climate Summaries</font> for <font color=red>$stationname, $state</font> to your shopping cart.  The price for this product is <Font color=red>\$2.00 for an individual station, \$6.00 for the entire state</font>, and the product will be
   delivered online soon after purchase.  You will be provided with an Adobe PDF file.</font><BR><BR>
OUTPUT

   }

print <<OUTPUT;
</font>
</TD>
</TR>
OUTPUT

   
   print <<OUTPUT;
<TR>
<TD align=CENTER valign=top>
</font>
OUTPUT
   if ($prodtype ne "CLIM20") {

      print <<OUTPUT;
<a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=olstore&subrnum=$subrnum&filename=$filename&type=PDF&prodtype=$prodtype" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('PDF','','http://www5.ncdc.noaa.gov/climaps2/shopcart2.JPG',1)"><img name="PDF" border="0" src="http://www5.ncdc.noaa.gov/climaps2/shopcart.JPG" alt="Add To Shopping Cart"></a>
OUTPUT
   
   } else {

            print <<OUTPUT;
<a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=olstore&subrnum=$subrnum&filename=$stateabb$filename&type=PDF&prodtype=$prodtype\.stn"><B>\$2.00 - Add Single Station PDF To Shopping Cart.</B></a> <BR><BR>
<a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=olstore&subrnum=$subrnum&filename=$stateabb&type=PDF&prodtype=$prodtype\.state"><B>\$6.00 - Add Entire State PDF To Shopping Cart.</B></a>
OUTPUT


   }

   print '</TD>';

}
   print '</TR></table>';

   return;

}

#-------------------------------------------------------------------------------------------------------
# results
#-------------------------------------------------------------------------------------------------------

sub quick_results {

   if(defined($dbh)) { $rc=$dbh->disconnect; }
   
   do_oracle_connect();

   if ($pop eq 'YES') {
      
      $qrescount = $dbh->prepare(qq{ select count(*) from climaps where popular='Y' and avail='Y'});
      $qrescount->execute;
      $c_rec = $qrescount->bind_columns(undef, \$hits);

      $qres = $dbh->prepare(qq{ select period, description, category, filename, popular from climaps where popular='Y'  and avail='Y' order by category, filename asc});
      $qres->execute;
      $c_rec = $qres->bind_columns(undef, \$period,\$description,\$category,\$filename,\$popular);

   
   } else {

      $qrescount = $dbh->prepare(qq{ select count(*) from climaps where category=upper('$category') and description='$description' and avail='Y'});
      $qrescount->execute;
      $c_rec = $qrescount->bind_columns(undef, \$hits);
      
      $qres = $dbh->prepare(qq{ select period, description, category, filename, popular from climaps where category=upper('$category') and description='$description'  and avail='Y' order by filename asc});
      $qres->execute;
      $c_rec = $qres->bind_columns(undef, \$period,\$description,\$category,\$filename,\$popular);
      
   }

   while ($qrescount -> fetch) {}
   $qrescount->finish();

   climatenormals_web_header('quick_results','qresults.jpg','','Quick Search - Results');

   if ($pop eq 'YES') {

         print <<OUTPUT;
<BR><font size=-1><I><B>$hits</B> map(s) found for <font color=blue><B>Most Popular</B></font>.</I></font><BR>
OUTPUT

   } else {

         print <<OUTPUT;
<BR><font size=-1><I><B>$hits</B> map(s) found for <font color=blue><B>$category - $description</B></I></font><BR>
OUTPUT
     
   }

   if ($hits eq 0) {

      print '<BR><BR><BR><B>&nbsp&nbsp&nbsp&nbsp&nbsp&nbspSorry!  There were no items which satisfied your search selections.  Please go back and try again.</B>';

   } else {
      
      print <<OUTPUT;
<B><font size=-1><I>Click on a map description from the list below to continue</I></B></font>
<table width=550 border=0 cellpadding=0>
<TR>
<TD>
<font size=-1>
<B><U>Map Description</U></B>
</font>
</TD>
<TD>
</TD>
</TR>
OUTPUT

      while ($qres -> fetch) {
      print <<OUTPUT;
<TR>
<TD valign=top>
OUTPUT

      if ($popular eq 'Y') {

         print '<img src="http://www.ncdc.noaa.gov/pub/data/images/mostpop.gif" border=0 alt="Most Popular / Most Requested">';
      }

      print <<OUTPUT;
<font size=-1>
<A href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=order_details&subrnum=$subrnum&filename=$filename"" target="_orderwindow">$category - $description ($period)</a>
</font>
</TD>
OUTPUT

      print <<OUTPUT;
</TR>
OUTPUT

   }

      print <<OUTPUT;
</table>
OUTPUT
   
   }

   $qres->finish();
   
   return;

}

#-------------------------------------------------------------------------------------------------------
# help
#-------------------------------------------------------------------------------------------------------

sub help {

   climatenormals_web_header('help','helptxt.jpg','','Help/Information/Links');

   print <<OUTPUT;
OUTPUT

   return;

}

#-------------------------------------------------------------------------------------------------------
# overview
#-------------------------------------------------------------------------------------------------------

sub overview {

   climatenormals_web_header('overview','overviewtxt.jpg','','Climate Normals Overview');

   print <<OUTPUT;
<font size=-1><BR>
   Climate is an important factor in agriculture, commerce, industry, and transportation.  
It affects many human activities such as farming, fuel consumption, structural design, building site location, 
trade, analysis of market fluctuations, and the utilization of other natural resources.  The influence of climate on our lives is endless. The National Oceanic and Atmospheric Administration's (NOAA's) National Climatic Data Center (NCDC) has a responsibility to fulfill the mandate of Congress "... to establish and record the climatic conditions of the United States."  This responsibility stems from a provision of the Organic Act of October 1, 1890, which established the Weather Bureau as a civilian agency (15 U.S.C. 311). 
<BR><BR>
The mandate to describe the climate was combined with guidelines established through 
international agreement.  The United Nation's World Meteorological Organization (WMO) 
requires the calculation of normals every 30 years, with the latest covering the 
1961-1990 period.  However, many WMO members, including the United States, update their 
normals at the completion of each decade.  <A HREF="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=prod_select&subrnum=$subrnum">
Newly computed climate normals products</A> are now available at NCDC for the 1971 to 2000 period.  
<BR><BR>
   The average value of a meteorological element over 30 years is defined as a 
climatological normal. The normal climate helps in describing the climate 
and is used as a base to which current conditions can be compared.  
Every ten years, NCDC computes new thirty-year climate normals for selected 
temperature and precipitation elements for a large number of U.S. climate and 
weather stations. These normals are summarized in daily, monthly, divisional, and 
supplementary normals products.
<BR><BR>
In the United States, normals have been computed for 1971-2000, 1961-1990, 1951-1980, 
1941-1970, 1931-1960, and 1921-1950. The normals from 1931-1960 to present are 
digitally archived by NCDC. The 1921-1950 normals, which were the first normals set 
prepared according to WMO standards, were published in 1956 as Weather Bureau Technical 
Paper No. 31 (Monthly Normal Temperatures, Precipitation, and Degree Days). 
This technical paper is <A HREF="http://www.ncdc.noaa.gov/oa/about/ncdcordering.html">available from NCDC</A> on microfiche. 
<BR><BR>
These earlier normals have been summarized in previous editions of the CLIM81, CLIM84, 
CLIM85, and CLIM20 publications. In addition, a comprehensive <A HREF="http://www.ncdc.noaa.gov/oa/about/cdrom/climatls1/info/atlasad.html">Climate Atlas of the 
United States </a>incorporates the 1961-1990 normals in a series of maps available in 
digital and hardcopy format.  These 1961-1990 maps are also available online in NCDC's <A HREF="http://www.nndc.noaa.gov/cgi-bin/climaps/climaps_dev.pl?directive=welcome">Climate Maps of the United States (CLIMAPS)</A>
 web system.  The 1951-80 freeze/frost probabilities have been 
summarized in a separate volume; and selected normals summaries have been collated 
into individual state volumes. 
</font>
OUTPUT

   return;

}


#-------------------------------------------------------------------------------------------------------
# subscription
#-------------------------------------------------------------------------------------------------------

sub subscription {

   climatenormals_web_header('subscription','subscriptioninfo.jpg','','Subscription Information');

   print <<OUTPUT;
<Table width=550 border=0 cellpadding=0>
<TR>
<TD valign=top>
<font size=-1>
Purchasing a subscription to a climate normals product will give you unlimited access to all of the publications for that product for a year, starting at the date of purchase.  To subscribe to a <B>newly computed (1971-2000) climate normals</B> publication for one year, click the "Add To Shopping Cart" button below for the publication you desire.
<B><font color=red>The price for each subscription is \$50.00</B></font>.<BR><BR>  </font>
</TD>
</TR>
</TR>
<TD align=center>
<a href="$olstoreurl?prod=C00117-PUB-A0001&subarr=002&quan=1&varname=subscription&varvalue=ALL" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('clim84Sub','','http://www5.ncdc.noaa.gov/climaps2/clim84subcartsel.jpg',1)"><img name="clim84Sub" border="0" src="http://www5.ncdc.noaa.gov/climaps2/clim84subcart.jpg" alt="Add Daily Station Normals Subscription To Shopping Cart"></a>
<a href="$olstoreurl?prod=C00115-PUB-S0001&subarr=008&quan=1&varname=subscription&varvalue=ALL" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('clim81Sub','','http://www5.ncdc.noaa.gov/climaps2/clim81subcartsel.jpg',1)"><img name="clim81Sub" border="0" src="http://www5.ncdc.noaa.gov/climaps2/clim81subcart.jpg" alt="Add Monthly Station Normals Subscription To Shopping Cart"></a>
<a href="$olstoreurl?prod=C00113-PUB-S0002&subarr=006&quan=1&varname=subscription&varvalue=ALL" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('clim20Sub','','http://www5.ncdc.noaa.gov/climaps2/clim20subcartsel.jpg',1)"><img name="clim20Sub" border="0" src="http://www5.ncdc.noaa.gov/climaps2/clim20subcart.jpg" alt="Add Monthly Station Climate Summaries To Shopping Cart"></a>
<BR><CENTER>
<a href="$gensuburl" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('AccessSub','','http://www5.ncdc.noaa.gov/climaps2/accesssub2.JPG',1)"><img name="AccessSub" border="0" src="http://www5.ncdc.noaa.gov/climaps2/accesssub.JPG" alt="Access Subscription Already Purchased"></a>
</CENTER>
</TD>
</TR>
</TABLE>
OUTPUT

return;

}

#-------------------------------------------------------------------------------------------------------
# subscription_error
#-------------------------------------------------------------------------------------------------------

sub subscription_error {

   climatenormals_web_header('subscription','subscriptioninfo.jpg','','Subscription Information');

   print <<OUTPUT;
<center>
<Table width=550 border=0 cellpadding=0>
<TR>
<TD valign=top>
<CENTER><font color=red><B>Notice!  Your Climate Normals subscription is either invalid or has expired.</FONT>
<BR><BR>To purchase a 1-year subscription please click on one of the buttons below.
</B>
</CENTER>
</TD>
</TR>
</TR>
<BR>
<TD colspan=2 align=center>
<BR>
<a href="$olstoreurl?prod=C00115-PUB-S0001&subarr=008&quan=1&varname=subscription&varvalue=ALL" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('clim81Sub','','http://www5.ncdc.noaa.gov/climaps2/clim81subcartsel.jpg',1)"><img name="clim81Sub" border="0" src="http://www5.ncdc.noaa.gov/climaps2/clim81subcart.jpg" alt="Add Monthly Station Normals Subscription To Shopping Cart"></a>
<a href="$olstoreurl?prod=C00117-PUB-A0001&subarr=002&quan=1&varname=subscription&varvalue=ALL" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('clim84Sub','','http://www5.ncdc.noaa.gov/climaps2/clim84subcartsel.jpg',1)"><img name="clim84Sub" border="0" src="http://www5.ncdc.noaa.gov/climaps2/clim84subcart.jpg" alt="Add Daily Station Normals Subscription To Shopping Cart"></a>
</TD>
</TR>
</TABLE>

OUTPUT

   return;

}

#-------------------------------------------------------------------------------------------------------
# prod_select
#-------------------------------------------------------------------------------------------------------

sub prod_select {

   climatenormals_web_header('prod_select','prodseltxt.jpg','','Climate Normals Product Selection');
   
   print <<OUTPUT;
<center>
<Table width=550 border=0 cellpadding=0>
<TR>
<TD valign=top>
<font size=-1>
OUTPUT


print <<OUTPUT;
<B>Climate normals products currently available online <A HREF="http://www.ncdc.noaa.gov/oa/climate/normals/usnormals.html"><font color=red>(detailed information)</font></a>:<BR></B>
<BR>
<B>* <IMG ALT="Very Small Yellow Image with the word NEW Displayed Inside" SRC="http://www.ncdc.noaa.gov/images/new.gif"><a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=prod_select2&prodtype=CLIM20&subrnum=$subrnum">Monthly Station Climate Summaries</B> (CLIM20)</A><br>
<B>* <a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=prod_select2&prodtype=CLIM84&subrnum=$subrnum">Daily Station Normals 1971-2000</B> (CLIM84)</A><BR>  
<B>* <a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=prod_select2&prodtype=CLIM81&subrnum=$subrnum">Monthly Station Normals 1971-2000</B> (CLIM81)</A><br>
<B>* <IMG ALT="Very Small Image with the word FREE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/free.jpg"><a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=prod_select2&prodtype=CLIM8101&subrnum=$subrnum">Monthly Precipitation Probabilities 1971-2000</B> (CLIM81-01)</A><br>
<B>* <IMG ALT="Very Small Image with the word FREE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/free.jpg"><a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=prod_select2&prodtype=CLIM8102&subrnum=$subrnum">Annual Degree Days to Selected Bases 1971-2000</B> (CLIM81-02)</A><br> 
<B>* <IMG ALT="Very Small Image with the word FREE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/free.jpg"><a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=prod_select2&prodtype=CLIM85&subrnum=$subrnum">Monthly Divisional Normals/Standard Deviations 1971-2000</B> (CLIM85)</A><br>
<font size=-1><B>* <IMG ALT="Very Small Yellow Image with the word NEW Displayed Inside" SRC="http://www.ncdc.noaa.gov/images/new.gif"><IMG ALT="Very Small Image with the word FREE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/free.jpg"><a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=prod_select2&prodtype=CLIM2001&subrnum=$subrnum">Frost/Freeze Data</B> (CLIM20-01)</A><br>
<font size=-1><B>* <IMG ALT="Very Small Yellow Image with the word NEW Displayed Inside" SRC="http://www.ncdc.noaa.gov/images/new.gif"><IMG ALT="Very Small Image with the word FREE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/free.jpg"><a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=prod_select2&prodtype=CLIM2002&subrnum=$subrnum">Snow Normals 1971-2000</B> (CLIM20-02)</A><br> 
<B>* <IMG ALT="Very Small Yellow Image with the word NEW Displayed Inside" SRC="http://www.ncdc.noaa.gov/images/new.gif"><IMG ALT="Very Small Image with the word FREE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/free.jpg"><a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=prod_select2&prodtype=HCS5&subrnum=$subrnum">Population-Weighted State, Regional, and National Monthly Degree Days</B> (HCS 5-1;2)</A>
<BR>
<B>* <IMG ALT="Very Small Yellow Image with the word NEW Displayed Inside" SRC="http://www.ncdc.noaa.gov/images/new.gif"><IMG ALT="Very Small Image with the word FREE Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/free.jpg"><a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=prod_select2&prodtype=HCS4&subrnum=$subrnum">Area-Weighted State, Regional, and National Temp. and Precip.</B> (HCS 4-1;2;3)</A>
<br> 
<B>* <IMG ALT="Very Small Image with the word NEW Displayed" SRC="http://www5.ncdc.noaa.gov/cdo/free.jpg"><A HREF="http://www.ncdc.noaa.gov/oa/documentlibrary/clim81supp3/clim81.html">Maps of Annual 1961-1990 Normal Temp., Precip. and Degree Days</B> (CLIM81-03)</A> 
</TD>
</TR>
</TABLE>
</CENTER>
OUTPUT


# <B>* <A HREF="http://www.ncdc.noaa.gov/oa/climate/normals/usnormalsprods.html#HCS5">Population-Weighted State, Regional, and National Monthly Degree Days</B> (HCS 5-1;2)</A><BR>

   return;

}

#-------------------------------------------------------------------------------------------------------
# important_notice
#-------------------------------------------------------------------------------------------------------

sub important_notice {

   climatenormals_web_header('important_notice','prodseltxt.jpg','','Climate Normals Important Notice');
   
   print <<OUTPUT;
<B><BR><CENTER>February 12, 2001</B><BR>

<B><font color=red>Important notice to customers who have recently obtained<BR>
U.S. Monthly Station Normals 1971-2000</font></b></CENTER><BR>


<font size=-1>The National Climatic Data Center (NCDC) has reissued the U.S. 
Monthly Climate Normals 1971-2000 that you recently obtained.  
This update was necessary due to errors detected in the ASCII files for selected states.  
There were also corrections made to the PDF and ASCII files for a 
small number of monthly heating/cooling degree day values for selected states.  <B>Please refer to the list below to determine if any of the
data you obtained contained errors.</B>  For additional details on the corrections, please refer to the ‘Errata' section 
of the normals web page at <A HREF="http://www.ncdc.noaa.gov/oa/climate/normals/usnormals.html">http://www.ncdc.noaa.gov/oa/climate/normals/usnormals.html</A>

<BR><BR><U><B>Affected States / Products:</B></U><BR><BR>

Alabama - PDF <BR>
Arizona - ASCII  <BR>
California - ASCII, PDF  <BR>
Colorado - ASCII, PDF <BR>
Florida - ASCII, PDF  <BR>
Georgia - PDF    <BR>
Kansas - PDF  <BR>      
Louisiana - ASCII, PDF <BR>
Maryland - ASCII  <BR>
Mississippi - ASCII, PDF <BR>
Nevada - ASCII, PDF  <BR>
New Jersey - ASCII, PDF  <BR>
North Carolina - ASCII, PDF <BR>
Oklahoma - PDF    <BR>
Oregon - PDF  <BR>
Pennsylvania - ASCII   <BR>  
South Carolina - ASCII, PDF <BR>
Texas - ASCII, PDF <BR>
Utah - PDF    <BR>
Virginia - ASCII, PDF  <BR>
Washington - ASCII, PDF  <BR>
Wyoming - PDF  <BR>
Alaska - PDF   <BR>
Hawaii - ASCII  <BR>
Puerto Rico - PDF   <BR>
<BR>
If you have any questions about these updates, 
please contact our normals technical contact, 
Tim Owen, at (828) 271-4358 (e-mail: Tim.Owen\@noaa.gov). 

<BR><BR> 
NCDC regrets any inconvenience that these changes have caused for our user community.</font>
OUTPUT

   return;

}

     
sub state_pop {

   my $poptitle = shift;
   my $size = shift;
   my $all = shift;
   my $selected = shift;
   
   if(defined($dbh)) { $rc=$dbh->disconnect; }
   
   do_oracle_connect();

   $states = $dbh->prepare(qq{ select distinct state from climate_normals where $prodtype='Y' order by state asc});
   $states->execute;
   $c_rec = $states->bind_columns(undef, \$state);
   
   if ($poptitle ne '') {
      
      print <<OUTPUT;
<B><label for='statelist'>$poptitle</label></B><BR>
OUTPUT

   }
   print <<OUTPUT;
<Select name="state" size="$size" id='statelist'>
OUTPUT
   
   $count=1;

   while ($states -> fetch) {
      
      if ($count == 1) {

         print '<option SELECTED value="'.$state.'"> '.$state;

      } else {

         print '<option  value="'.$state.'"> '.$state;
   
      }

      $count++;
      
   }
      
   $states->finish();

   print '</select>';

   return;

}

sub station_pop {

   my $poptitle = shift;
   my $size = shift;
   my $all = shift;
   my $selected = shift;
   
   if(defined($dbh)) { $rc=$dbh->disconnect; }
   
   do_oracle_connect();

   $stations = $dbh->prepare(qq{ select distinct filename, station_name, decode(RTRIM(LTRIM(elems)),'P','(P)','') from climate_normals where state='$state' and $prodtype='Y' order by station_name asc});
   $stations->execute;
   $c_rec = $stations->bind_columns(undef, \$filename, \$station,\$elems);
   
   if ($poptitle ne '') {
      
      print <<OUTPUT;
<B><label for='stationlist'>$poptitle</label></B><BR>
OUTPUT

   }

   print <<OUTPUT;
<Select name="station" size="$size" id='stationlist'>
OUTPUT

   $count=1;
   
   while ($stations -> fetch) {
      
      if ($count == 1) {

         print '<option SELECTED value="'.$filename.'"> '.$station.' '.$elems;
      
      } else {

         print '<option value="'.$filename.'"> '.$station.' '.$elems;
   
      }

      $count++;

   }
      
   $stations->finish();

   print '</select>';

   return;

}


sub climatenormals_web_header {

   my $callsub = shift;
   my $txtimg = shift;
   my $descript = shift;
   my $alttag = shift;

   print <<OUTPUT;
<HTML lang='en'>
<HEAD>
<TITLE>NCDC: U.S. Climate Normals - $descript</TITLE>
<meta name="author" content="Douglas P. Ross">
<script language="JavaScript">
<!--
function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_findObj(n, d) { //v3.0
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}

function go()
{
	box = document.forms[0].navi;
	destination = box.options[box.selectedIndex].value;
	if (destination) location.href = destination;
}

//-->
</script>
</HEAD>

<BODY BGCOLOR=white>
<CENTER>
<Table border=0 width=650 BGCOLOR=white>
<TR>
<TD colspan=3 align=center>
<EM>
<B>
<A HREF="#SKIP1"><IMG BORDER=0 SRC="http://www.ncdc.noaa.gov/images/noaabullet.gif" alt="Small NOAA logo image / Skip Tool Bar"></A>
<A HREF="http://www.ncdc.noaa.gov/">NCDC</a> /
        <A HREF="http://www.ncdc.noaa.gov/ol/climate/climateresources.html">Climate</A> / <A HREF="http://www.ncdc.noaa.gov/ol/climate/climatedata.html">Get/View</A></A> / U.S. Climate Normals / <A HREF="http://www.ncdc.noaa.gov/ol/about/ncdcsearch.html">Search</A> / <A HREF="http://www.ncdc.noaa.gov/oa/about/ncdchelp.html">Help</A></B>
</EM>
<A NAME="SKIP1"></A>
<HR>
</TD>
</TR>
<TR height=200>
<TD valign=top>
<CENTER>
OUTPUT

   if ($callsub eq 'prod_select') {
   
      print <<OUTPUT;
<img name="CLINORMS" border="0" src="http://www5.ncdc.noaa.gov/climaps2/clinormslogo.jpg" alt="U.S. Climate Normals (Logo)"><BR>
<IMG SRC="http://www5.ncdc.noaa.gov/cdo/smline.gif" border=0 alt="Thin line seperating Climate Normals Logo from Climate Normals Tool Bar"><BR>
OUTPUT
   
   } else {

      print <<OUTPUT;
<a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=prod_select&subrnum=$subrnum"><img name="CLINORMS" border="0" src="http://www5.ncdc.noaa.gov/climaps2/clinormslogo.jpg" alt="U.S. Climate Normals (Logo)"></a><BR>
<IMG SRC="http://www5.ncdc.noaa.gov/cdo/smline.gif" border=0 alt="Thin line seperating Climate Normals Logo from Climate Normals Tool Bar"><BR>
OUTPUT

   }
   if ($callsub eq 'overview') {

         print <<OUTPUT;
<img name="overview" border="0" src="http://www5.ncdc.noaa.gov/climaps2/overview3.jpg" width="100" height="30" alt="Climate Normals Overview"><BR>
OUTPUT

      
    } else {
      
         print <<OUTPUT;
<a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=overview&subrnum=$subrnum" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('overview','','http://www5.ncdc.noaa.gov/climaps2/overview2.jpg',1)"><img name="overview" border="0" src="http://www5.ncdc.noaa.gov/climaps2/overview.jpg" width="100" height="30" alt="Climate Normals Overview"></a><BR>
OUTPUT

    }

    if ($callsub eq 'prod_select') {

         print <<OUTPUT;
<img name="prodsel" border="0" src="http://www5.ncdc.noaa.gov/climaps2/prodselect3.jpg" width="100" height="46" alt="Climate Normals Product Selection"><BR>
OUTPUT

      
    } else {
      
         print <<OUTPUT;
<a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=prod_select&subrnum=$subrnum" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('prodsel','','http://www5.ncdc.noaa.gov/climaps2/prodselect2.jpg',1)"><img name="prodsel" border="0" src="http://www5.ncdc.noaa.gov/climaps2/prodselect.jpg" width="100" height="46" alt="Climate Normals Product Selection"></a><BR>
OUTPUT

    }
      
   if ($callsub eq 'subscription') {

      print <<OUTPUT;
<img name="Subscription" border="0" src="http://www5.ncdc.noaa.gov/climaps2/unlimited3.jpg" width="100" height="46" alt="Unlimited Access Information"><BR>
OUTPUT

   } else {


      print <<OUTPUT;
<a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=subscription&subrnum=$subrnum" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('unlimited','','http://www5.ncdc.noaa.gov/climaps2/unlimited2.jpg',1)"><img name="unlimited" border="0" src="http://www5.ncdc.noaa.gov/climaps2/unlimited.jpg" width="100" height="46" alt="Unlimited Access Information"></a><BR>
OUTPUT

   }

   print <<OUTPUT;
<a href="http://www.ncdc.noaa.gov/oa/climate/normals/usnormals.html#nws" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('NWS','','http://www5.ncdc.noaa.gov/climaps2/nws2.jpg',1)"><img name="NWS" border="0" src="http://www5.ncdc.noaa.gov/climaps2/nws.jpg" width="100" height="46" alt="National Weather Service (NWS) Inquiries"></a><BR>
OUTPUT
   
 
#print <<OUTPUT;
#<font size=-1>TESTING:<BR>
#Valid Sub: $validsub<BR>
#Expired Sub: $subexp<BR>
#Chargetype: $chargetype<BR></font>
#OUTPUT

if ($validsub eq 'YES') {

   print <<OUTPUT;
<IMG SRC="http://www5.ncdc.noaa.gov/cdo/smline.gif" border=0 alt="Thin line seperating Climate Normals Logo from Climate Normals Tool Bar"><BR>
<font size=-1><font color=RED><B>Subscription User<BR></font>
<IMG SRC="http://www5.ncdc.noaa.gov/cdo/smline.gif" border=0 alt="Thin line seperating Climate Normals Logo from Climate Normals Tool Bar"><BR>
Expiration Date: <Font color=red>$expdate</font></font></B>
OUTPUT
   
} else {

   if ($callsub eq 'prod_select') {

      print <<OUTPUT
<IMG SRC="http://www5.ncdc.noaa.gov/cdo/smline.gif" border=0 alt="Thin line seperating Climate Normals Logo from Climate Normals Tool Bar"><BR>
<img name="CLINORMS" border="0" src="http://www5.ncdc.noaa.gov/climaps2/clinormslogo2.jpg" alt="U.S. Climate Normals (Logo)"><BR>
OUTPUT


   } else {

      print <<OUTPUT;
<IMG SRC="http://www5.ncdc.noaa.gov/cdo/smline.gif" border=0 alt="Thin line seperating Climate Normals Logo from Climate Normals Tool Bar"><BR>
<a href="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname?directive=prod_select&subrnum=$subrnum"><img name="CLINORMS" border="0" src="http://www5.ncdc.noaa.gov/climaps2/clinormslogo2.jpg" alt="U.S. Climate Normals (Logo)"></a><BR>
OUTPUT

   }

}

      print <<OUTPUT;
<CENTER>
</TD>
<TD height=200 valign=top>
<IMG SRC="http://www5.ncdc.noaa.gov/climaps2/line.gif" alt="Vertical line seperating Climate Normals Tool Bar from Climate Normals web page body"><BR>
</TD>
<TD valign=top>
<IMG SRC="http://www5.ncdc.noaa.gov/climaps2/climatenormals.jpg" alt="U.S. Climate Normals"><BR>
OUTPUT

if ($txtimg ne 'N/A') {

     print <<OUTPUT;
<IMG SRC="http://www5.ncdc.noaa.gov/climaps2/$txtimg" alt="$alttag">
OUTPUT

   }
   
   return;

}

sub climatenormals_web_footer {

   print <<OUTPUT;
</TD>
</TR>
<TR>
<TD colspan=3>
<HR>
<CENTER>
<EM><B>
<A HREF="#SKIP2"><IMG BORDER=0 SRC="http://www.ncdc.noaa.gov/images/noaabullet.gif" alt="Small NOAA logo image / Skip Tool Bar"></A>
<A HREF="http://www.ncdc.noaa.gov/">NCDC</a> /
        <A HREF="http://www.ncdc.noaa.gov/ol/climate/climateresources.html">Climate</A> / <A HREF="http://www.ncdc.noaa.gov/ol/climate/climatedata.html">Get/View</A></A> / U.S. Climate Normals / <A HREF="http://www.ncdc.noaa.gov/ol/about/ncdcsearch.html">Search</A> / <A HREF="http://cdo.ncdc.noaa.gov/cdo/info.html">Help</A></CENTER>
<BR>
</B>
<P>
<em>
<I>This page was dynamically generated on $stime via<BR>
http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname
<BR>
</em>
<A NAME="SKIP2"></A>
</I>
</TD>
</TR>
</TABLE>
</BODY>
</HTML>
OUTPUT

   return;

}

sub form_tag {

   my $tagdirect = shift;

   print <<OUTPUT;
<FORM ACTION="http://www5.ncdc.noaa.gov/cgi-bin/climatenormals/$progname" METHOD="POST">
<input type=hidden name="directive" value="$tagdirect">
OUTPUT

   return;

}

#-------------------------------------------------------------------------------------------------------
# check_subscription - Find out if this is a valid/current subscription user......
#-------------------------------------------------------------------------------------------------------

sub check_subscription {

   $checkdate_CLIM81 = $dbh->prepare(qq{SELECT random_number, to_char(end_date,'MON DD, YYYY') from CLIM81_ONLINE_DATASET_SUB where random_number='$subrnum' and product_id='CLIM81' and SYSDATE <= end_date});
   
   $checkdate_CLIM81->execute();

   while ((@rec)=$checkdate_CLIM81->fetchrow()) {

         $subdatecheck = @rec[0];
         $expdate = @rec[1];

   }

   $checkdate_CLIM81->finish();

   if ($subdatecheck == $subrnum) {

         $subexp='NO';
         $validsub='YES';

         if ($prodtype eq 'CLIM81') {
            
            $chargetype='FREE';

         }

         return;

   } else {

         
      $checkdate_CLIM84 = $dbh->prepare(qq{SELECT random_number, to_char(end_date,'MON DD, YYYY') from CLIM84_ONLINE_DATASET_SUB where random_number='$subrnum' and product_id='CLIM84' and SYSDATE <= end_date});
      
      $checkdate_CLIM84->execute();
   
      while ((@rec)=$checkdate_CLIM84->fetchrow()) {
   
            $subdatecheck = @rec[0];
            $expdate = @rec[1];
   
      }
   
      $checkdate_CLIM84->finish();
   
      if ($subdatecheck == $subrnum) {
   
            $subexp='NO';
            $validsub='YES';

            if ($prodtype eq 'CLIM84') {
            
               $chargetype='FREE';

            }

            return;
      }

      $checkdate_CLIM20 = $dbh->prepare(qq{SELECT random_number, to_char(end_date,'MON DD, YYYY') from CLIM20_ONLINE_DATASET_SUB where random_number='$subrnum' and product_id='CLIM20' and SYSDATE <= end_date});
      
      $checkdate_CLIM20->execute();
   
      while ((@rec)=$checkdate_CLIM20->fetchrow()) {
   
            $subdatecheck = @rec[0];
            $expdate = @rec[1];
   
      }
   
      $checkdate_CLIM20->finish();
   
      if ($subdatecheck == $subrnum) {
   
            $subexp='NO';
            $validsub='YES';

            if ($prodtype eq 'CLIM20') {
            
               $chargetype='FREE';

            }

      } else {
   
            $subexp='YES';
            $validsub='NO';
   
      }
   
   }
   
   return;

}



#-------------------------------------------------------------------------------------------------------
# get_chargetype - Get Chargetype from stored oracle function......
#-------------------------------------------------------------------------------------------------------

sub get_chargetype {

        $charge = $dbh->prepare( "

		SELECT
			cdolib.chargetype('$forceoutside','$udomain','$ipaddress')
		FROM
			dual" );
	$charge->execute();

	while ((@rec)=$charge->fetchrow()) {
	
		$chargetype = @rec[0];
	
	}

	$charge->finish();
	return;

}

#-------------------------------------------------------------------------------------------------------
# update_climaps_stats 
#-------------------------------------------------------------------------------------------------------

sub update_climaps_stats {

	my $add_to_climaps_stats = $dbh->do(qq{insert into climaps_stats values ('$directive','$chargetype','$ipaddress','$udomain',SYSDATE)});

	$rv  = $dbh->commit; 

	return;

}

#-------------------------------------------------------------------------------------------------------
# do_oracle_connect- Connects to Oracle database on Arid workstation for data queries
#-------------------------------------------------------------------------------------------------------

sub do_oracle_connect{

#-------------------------------------------------------------------------------------------------------
# Set up parameters
#-------------------------------------------------------------------------------------------------------

   $dbname=$dbasename;
   $user=$schemaname;
   $password=$schemapass;
   $dbd='Oracle';

#-------------------------------------------------------------------------------------------------------
# Open DBI connection
#-------------------------------------------------------------------------------------------------------

   $dbh=DBI->connect($dbname,$user,$password,$dbd);

   if (!$dbh) {

      print "Error connecting to database $DBI::errstr\n";

   }

   return;

}

#-------------------------------------------------------------------------------------------------------
# do_oracle_connect- Connects to Oracle database on Arid workstation for data queries
#-------------------------------------------------------------------------------------------------------

sub oracle_connect_charge{

#-------------------------------------------------------------------------------------------------------
# Set up parameters
#-------------------------------------------------------------------------------------------------------

   $dbname=$c_dbasename;
   $user=$c_schemaname;
   $password=$c_schemapass;
   $dbd='Oracle';

#-------------------------------------------------------------------------------------------------------
# Open DBI connection
#-------------------------------------------------------------------------------------------------------

   $dbh=DBI->connect($dbname,$user,$password,$dbd);

   if (!$dbh) {

      print "Error connecting to database $DBI::errstr\n";

   }

   return;

}




