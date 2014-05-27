describe('Check glider generation', function() {
  it('should filter results', function() {
    // Find the first (and only) button on the page and click it
    element(by.css('#next_gen_btn')).click();
    element(by.css('#next_gen_btn')).click();
    element(by.css('#next_gen_btn')).click();
    element(by.css('#next_gen_btn')).click();

    // Verify that now there is only one item in the task list
    expect(element.all(by.css('#current-states')).getText()).toEqual({"43":{"42":1,"43":1,"50":1,"51":1},"44":{"42":1,"44":1,"49":1,"51":1},"45":{"42":1,"51":1},"53":{"42":1,"51":1},"54":{"42":1,"44":1,"49":1,"51":1},"55":{"42":1,"43":1,"50":1,"51":1}});
  });
});