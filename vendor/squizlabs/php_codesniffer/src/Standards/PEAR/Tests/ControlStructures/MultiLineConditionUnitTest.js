if (blah(param)) {

}

if ((condition1
    || condition2)
    && condition3
    && condition4
    && condition5
) {
}

if ((condition1 || condition2) && condition3 && condition4 && condition5) {
}

if ((condition1 || condition2)
    && condition3
) {
}

if (
    (condition1 || condition2)
    && condition3
) {
}

if ((condition1
    || condition2)
) {
}

if ((condition1
    || condition2)
    && condition3 &&
    condition4
) {
}

if ((condition1
   || condition2)
      && condition3
   && condition4
   && condition5
) {
}

if (($condition1
    || $condition2)
)  {
}

if ((condition1
    || condition2)
) {
}

if (
    (
    condition1
    || condition2
   )
    && condition3
) {
}


if (  condition1
    || condition2
    || condition3
) {
}

if (condition1
    || condition2
    || condition3
) {
} else if (condition1
    || condition2
    || condition3
) {
}

if (condition1
    || condition2
    || condition3
) {
} else if (
    condition1
   || condition2 &&
    condition3
) {
}

if (condition1
    || condition2
|| condition3) {
}

if (condition1
    || condition2 || condition3
){
}

if (condition1)
    console.info('bar');

if (condition1
    || condition2
|| condition3)
    console.info('bar');


if (condition1
    || condition2 || condition3
)
    console.info('bar');

if (!a(post)
    && (!a(context.header)
    ^ a(context.header, 'Content-Type'))
) { 
// ...
}

if (foo)
{
    console.info('bar');
}

// Should be no errors even though lines are
// not exactly aligned together. Multi-line function
// call takes precedence.
if (array_key_exists(key, value)
    && foo.bar.baz(
        key, value2
   )
) {
}

if (true) {
    foo = true;
};

if (foo == 401 || // comment
    bar == 3200)  /* long comment
                     here
                   */
{
    return false;
}

if (foo == 401 || // comment
    bar == 3200)  // long comment here
{
    return false;
}

if (IPP.errorCode() == 401
    // Comment explaining the next condition here.
    || IPP.errorCode() == 3200
) {
    return false;
}

function bar() {
    if (a
        && b
) {
        return false;
    }
}

if (a
    && foo(
        'a',
        'b'
   )) {
    return false;
}













if (foo == 401 || // phpcs:ignore Standard.Category.Sniff -- for reasons.
    bar == 3200)  /*
                     phpcs:ignore Standard.Category.Sniff -- for reasons.
                   */
{
    return false;
}

if (foo == 401 || // phpcs:disable Standard.Category.Sniff -- for reasons.
    bar == 3200)  // phpcs:enable
{
    return false;
}

if (IPP.errorCode() == 401
    // phpcs:ignore Standard.Category.Sniff -- for reasons.
    || IPP.errorCode() == 3200
) {
    return false;
}

    if (foo == 401 ||
    /*
	 * phpcs:disable Standard.Category.Sniff -- for reasons.
	 */
    bar == 3200
   ) {
        return false;
    }

if (IPP.errorCode() == 401
    || IPP.errorCode() == 3200
    // phpcs:ignore Standard.Category.Sniff -- for reasons.
) {
    return false;
}

if (foo == 401
    || bar
        == 'someverylongexpectedoutput'
) {
    return false;
}

if (IPP.errorCode() == 401
    || bar
        // A comment.
        == 'someverylongexpectedoutput'
) {
    return false;
}

if (foo == 401
    || IPP.errorCode()
        // phpcs:ignore Standard.Category.Sniff -- for reasons.
        == 'someverylongexpectedoutput'
) {
    return false;
}
